use axum::{
    http::StatusCode,
    response::Json,
    routing::{delete, get, post, put},
    Router,
};
use serde_json::{json, Value};
use std::env;
use tower_http::cors::CorsLayer;
use tracing_subscriber::{layer::SubscriberExt, util::SubscriberInitExt};

mod config;
mod database;
mod handlers;
mod middleware;
mod models;
mod services;

#[tokio::main]
async fn main() -> anyhow::Result<()> {
    // 환경 변수 로드
    dotenvy::dotenv().ok();

    // 로깅 초기화
    tracing_subscriber::registry()
        .with(
            tracing_subscriber::EnvFilter::try_from_default_env()
                .unwrap_or_else(|_| "sample_intranet_rust=debug,tower_http=debug".into()),
        )
        .with(tracing_subscriber::fmt::layer())
        .init();

    // 설정 로드
    let config = config::Config::from_env()?;
    tracing::info!("설정 로드 완료: {:?}", config);

    // 데이터베이스 연결
    let db_pool = database::create_pool(&config.database_url).await?;
    database::run_migrations(&db_pool).await?;
    tracing::info!("데이터베이스 초기화 완료");

    // 애플리케이션 상태
    let app_state = AppState { db_pool };

    // 라우터 설정
    let app = create_router(app_state);

    // 서버 시작
    let listener = tokio::net::TcpListener::bind(&config.server_address).await?;
    tracing::info!("서버 시작: {}", config.server_address);

    axum::serve(listener, app).await?;

    Ok(())
}

// 애플리케이션 상태
#[derive(Clone)]
pub struct AppState {
    pub db_pool: sqlx::PgPool,
}

// 라우터 생성
fn create_router(state: AppState) -> Router {
    Router::new()
        // 헬스체크 엔드포인트
        .route("/health", get(health_check))
        // API v1 라우트
        .nest("/api/v1", api_routes())
        .layer(CorsLayer::permissive()) // 개발 환경용 CORS 설정
        .with_state(state)
}

// API 라우트 정의
fn api_routes() -> Router<AppState> {
    Router::new()
        // 인증 라우트
        .nest("/auth", auth_routes())
        // 사용자 라우트 (인증 필요)
        .nest("/users", user_routes())
        // 관리자 라우트 (관리자 권한 필요)
        .nest("/admin", admin_routes())
}

// 인증 라우트
fn auth_routes() -> Router<AppState> {
    Router::new()
        .route("/register", post(handlers::auth::register))
        .route("/login", post(handlers::auth::login))
}

// 사용자 라우트
fn user_routes() -> Router<AppState> {
    Router::new()
        .route("/", get(handlers::users::get_users))
        .route("/:id", get(handlers::users::get_user))
        .route("/:id", put(handlers::users::update_user))
        .route_layer(axum::middleware::from_fn(middleware::auth_middleware))
}

// 관리자 라우트
fn admin_routes() -> Router<AppState> {
    Router::new()
        .route("/users/:id", delete(handlers::users::delete_user))
        .route_layer(axum::middleware::from_fn(middleware::admin_middleware))
        .route_layer(axum::middleware::from_fn(middleware::auth_middleware))
}

// 헬스체크 핸들러
async fn health_check() -> Json<Value> {
    Json(json!({
        "status": "healthy",
        "timestamp": chrono::Utc::now().timestamp()
    }))
} 