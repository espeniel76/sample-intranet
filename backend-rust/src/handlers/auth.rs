use axum::{
    extract::State,
    http::StatusCode,
    response::{IntoResponse, Json},
};
use validator::Validate;

use crate::{
    middleware::create_jwt_token,
    models::{AuthResponse, ErrorResponse, LoginRequest, RegisterRequest, UserResponse},
    services::UserService,
    AppState,
};

// 회원가입 핸들러
pub async fn register(
    State(state): State<AppState>,
    Json(request): Json<RegisterRequest>,
) -> impl IntoResponse {
    // 요청 데이터 유효성 검사
    if let Err(errors) = request.validate() {
        let error_messages: Vec<String> = errors
            .field_errors()
            .into_iter()
            .flat_map(|(_, field_errors)| {
                field_errors.iter().map(|error| {
                    error.message.as_ref().unwrap_or(&"유효성 검사 실패".into()).to_string()
                })
            })
            .collect();
        
        return (
            StatusCode::BAD_REQUEST,
            Json(ErrorResponse::new("validation_error", &error_messages.join(", "))),
        ).into_response();
    }

    let user_service = UserService::new(state.db_pool);

    // 이메일 중복 확인
    match user_service.find_by_email(&request.email).await {
        Ok(Some(_)) => {
            return (
                StatusCode::CONFLICT,
                Json(ErrorResponse::new("email_exists", "이미 존재하는 이메일입니다")),
            ).into_response();
        }
        Ok(None) => {
            // 이메일이 존재하지 않음 (정상)
        }
        Err(err) => {
            tracing::error!("데이터베이스 오류: {}", err);
            return (
                StatusCode::INTERNAL_SERVER_ERROR,
                Json(ErrorResponse::new("database_error", "서버 오류가 발생했습니다")),
            ).into_response();
        }
    }

    // 사용자 생성
    match user_service.create_user(request).await {
        Ok(user) => {
            // JWT 토큰 생성
            match create_jwt_token(user.id, &user.email, &user.role) {
                Ok(token) => {
                    let response = AuthResponse {
                        token,
                        user: UserResponse::from(user),
                    };
                    (StatusCode::CREATED, Json(response)).into_response()
                }
                Err(err) => {
                    tracing::error!("JWT 토큰 생성 실패: {}", err);
                    (
                        StatusCode::INTERNAL_SERVER_ERROR,
                        Json(ErrorResponse::new("token_error", "토큰 생성에 실패했습니다")),
                    ).into_response()
                }
            }
        }
        Err(err) => {
            tracing::error!("사용자 생성 실패: {}", err);
            (
                StatusCode::INTERNAL_SERVER_ERROR,
                Json(ErrorResponse::new("creation_error", "사용자 생성에 실패했습니다")),
            ).into_response()
        }
    }
}

// 로그인 핸들러
pub async fn login(
    State(state): State<AppState>,
    Json(request): Json<LoginRequest>,
) -> impl IntoResponse {
    // 요청 데이터 유효성 검사
    if let Err(errors) = request.validate() {
        let error_messages: Vec<String> = errors
            .field_errors()
            .into_iter()
            .flat_map(|(_, field_errors)| {
                field_errors.iter().map(|error| {
                    error.message.as_ref().unwrap_or(&"유효성 검사 실패".into()).to_string()
                })
            })
            .collect();
        
        return (
            StatusCode::BAD_REQUEST,
            Json(ErrorResponse::new("validation_error", &error_messages.join(", "))),
        ).into_response();
    }

    let user_service = UserService::new(state.db_pool);

    // 사용자 조회
    let user = match user_service.find_by_email(&request.email).await {
        Ok(Some(user)) => user,
        Ok(None) => {
            return (
                StatusCode::UNAUTHORIZED,
                Json(ErrorResponse::new("invalid_credentials", "이메일 또는 비밀번호가 잘못되었습니다")),
            ).into_response();
        }
        Err(err) => {
            tracing::error!("데이터베이스 오류: {}", err);
            return (
                StatusCode::INTERNAL_SERVER_ERROR,
                Json(ErrorResponse::new("database_error", "서버 오류가 발생했습니다")),
            ).into_response();
        }
    };

    // 비밀번호 검증
    match user_service.verify_password(&request.password, &user.password) {
        Ok(true) => {
            // 활성 사용자 확인
            if !user.is_active {
                return (
                    StatusCode::FORBIDDEN,
                    Json(ErrorResponse::new("account_inactive", "비활성화된 계정입니다")),
                ).into_response();
            }

            // JWT 토큰 생성
            match create_jwt_token(user.id, &user.email, &user.role) {
                Ok(token) => {
                    let response = AuthResponse {
                        token,
                        user: UserResponse::from(user),
                    };
                    (StatusCode::OK, Json(response)).into_response()
                }
                Err(err) => {
                    tracing::error!("JWT 토큰 생성 실패: {}", err);
                    (
                        StatusCode::INTERNAL_SERVER_ERROR,
                        Json(ErrorResponse::new("token_error", "토큰 생성에 실패했습니다")),
                    ).into_response()
                }
            }
        }
        Ok(false) => {
            (
                StatusCode::UNAUTHORIZED,
                Json(ErrorResponse::new("invalid_credentials", "이메일 또는 비밀번호가 잘못되었습니다")),
            ).into_response()
        }
        Err(err) => {
            tracing::error!("비밀번호 검증 실패: {}", err);
            (
                StatusCode::INTERNAL_SERVER_ERROR,
                Json(ErrorResponse::new("verification_error", "비밀번호 검증에 실패했습니다")),
            ).into_response()
        }
    }
} 