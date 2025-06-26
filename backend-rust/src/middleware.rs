use axum::{
    http::{Request, StatusCode},
    middleware::Next,
    response::{IntoResponse, Response},
    Json,
};
use chrono::{Duration, Utc};
use jsonwebtoken::{decode, encode, DecodingKey, EncodingKey, Header, Validation};
use std::env;

use crate::models::{Claims, ErrorResponse};

// JWT 토큰 생성
pub fn create_jwt_token(user_id: i32, email: &str, role: &str) -> anyhow::Result<String> {
    let jwt_secret = env::var("JWT_SECRET")
        .unwrap_or_else(|_| "your-secret-key-change-in-production".to_string());
    
    let jwt_expires_hours = env::var("JWT_EXPIRES_HOURS")
        .unwrap_or_else(|_| "24".to_string())
        .parse::<i64>()
        .unwrap_or(24);

    let expiration = Utc::now()
        .checked_add_signed(Duration::hours(jwt_expires_hours))
        .expect("유효한 타임스탬프")
        .timestamp() as usize;

    let claims = Claims {
        sub: user_id.to_string(),
        email: email.to_string(),
        role: role.to_string(),
        exp: expiration,
    };

    let token = encode(
        &Header::default(),
        &claims,
        &EncodingKey::from_secret(jwt_secret.as_ref()),
    )?;

    Ok(token)
}

// JWT 토큰 검증
pub fn verify_jwt_token(token: &str) -> anyhow::Result<Claims> {
    let jwt_secret = env::var("JWT_SECRET")
        .unwrap_or_else(|_| "your-secret-key-change-in-production".to_string());

    let token_data = decode::<Claims>(
        token,
        &DecodingKey::from_secret(jwt_secret.as_ref()),
        &Validation::default(),
    )?;

    Ok(token_data.claims)
}

// 인증 미들웨어
pub async fn auth_middleware<B>(
    mut request: Request<B>,
    next: Next<B>,
) -> Result<Response, StatusCode> {
    // Authorization 헤더에서 토큰 추출
    let auth_header = request
        .headers()
        .get("Authorization")
        .and_then(|header| header.to_str().ok())
        .and_then(|header| {
            if header.starts_with("Bearer ") {
                Some(&header[7..])
            } else {
                None
            }
        });

    let token = match auth_header {
        Some(token) => token,
        None => {
            return Err(StatusCode::UNAUTHORIZED);
        }
    };

    // 토큰 검증
    let claims = match verify_jwt_token(token) {
        Ok(claims) => claims,
        Err(_) => {
            return Err(StatusCode::UNAUTHORIZED);
        }
    };

    // 요청에 사용자 정보 추가
    request.extensions_mut().insert(claims);

    Ok(next.run(request).await)
}

// 관리자 권한 확인 미들웨어
pub async fn admin_middleware<B>(
    request: Request<B>,
    next: Next<B>,
) -> Result<Response, StatusCode> {
    // 요청에서 사용자 정보 추출
    let claims = request
        .extensions()
        .get::<Claims>()
        .ok_or(StatusCode::UNAUTHORIZED)?;

    // 관리자 권한 확인
    if claims.role != "admin" {
        return Err(StatusCode::FORBIDDEN);
    }

    Ok(next.run(request).await)
}

// 현재 사용자 정보 추출 헬퍼 함수
pub fn get_current_user(request: &axum::http::Request<axum::body::Body>) -> Option<&Claims> {
    request.extensions().get::<Claims>()
} 