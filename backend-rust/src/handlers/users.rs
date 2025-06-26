use axum::{
    extract::{Path, State},
    http::StatusCode,
    response::{IntoResponse, Json},
    Extension,
};
use validator::Validate;

use crate::{
    models::{Claims, ErrorResponse, UpdateUserRequest, UserResponse},
    services::UserService,
    AppState,
};

// 사용자 목록 조회 핸들러
pub async fn get_users(
    State(state): State<AppState>,
    Extension(claims): Extension<Claims>,
) -> impl IntoResponse {
    let user_service = UserService::new(state.db_pool);

    match user_service.find_all().await {
        Ok(users) => {
            let user_responses: Vec<UserResponse> = users
                .into_iter()
                .map(UserResponse::from)
                .collect();

            (StatusCode::OK, Json(user_responses)).into_response()
        }
        Err(err) => {
            tracing::error!("사용자 목록 조회 실패: {}", err);
            (
                StatusCode::INTERNAL_SERVER_ERROR,
                Json(ErrorResponse::new("database_error", "서버 오류가 발생했습니다")),
            ).into_response()
        }
    }
}

// 특정 사용자 조회 핸들러
pub async fn get_user(
    State(state): State<AppState>,
    Path(user_id): Path<i32>,
    Extension(claims): Extension<Claims>,
) -> impl IntoResponse {
    let user_service = UserService::new(state.db_pool);

    match user_service.find_by_id(user_id).await {
        Ok(Some(user)) => {
            (StatusCode::OK, Json(UserResponse::from(user))).into_response()
        }
        Ok(None) => {
            (
                StatusCode::NOT_FOUND,
                Json(ErrorResponse::new("user_not_found", "사용자를 찾을 수 없습니다")),
            ).into_response()
        }
        Err(err) => {
            tracing::error!("사용자 조회 실패: {}", err);
            (
                StatusCode::INTERNAL_SERVER_ERROR,
                Json(ErrorResponse::new("database_error", "서버 오류가 발생했습니다")),
            ).into_response()
        }
    }
}

// 사용자 정보 수정 핸들러
pub async fn update_user(
    State(state): State<AppState>,
    Path(user_id): Path<i32>,
    Extension(claims): Extension<Claims>,
    Json(request): Json<UpdateUserRequest>,
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

    // 권한 확인: 본인 또는 관리자만 수정 가능
    let current_user_id: i32 = claims.sub.parse().unwrap_or(0);
    if current_user_id != user_id && claims.role != "admin" {
        return (
            StatusCode::FORBIDDEN,
            Json(ErrorResponse::new("permission_denied", "수정 권한이 없습니다")),
        ).into_response();
    }

    let user_service = UserService::new(state.db_pool);

    // 이메일 중복 확인 (이메일 수정 시)
    if let Some(ref email) = request.email {
        match user_service.find_by_email(email).await {
            Ok(Some(existing_user)) => {
                // 다른 사용자가 이미 해당 이메일을 사용 중인지 확인
                if existing_user.id != user_id {
                    return (
                        StatusCode::CONFLICT,
                        Json(ErrorResponse::new("email_exists", "이미 존재하는 이메일입니다")),
                    ).into_response();
                }
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
    }

    // 사용자 정보 수정
    match user_service.update_user(user_id, request).await {
        Ok(Some(user)) => {
            (StatusCode::OK, Json(UserResponse::from(user))).into_response()
        }
        Ok(None) => {
            (
                StatusCode::NOT_FOUND,
                Json(ErrorResponse::new("user_not_found", "사용자를 찾을 수 없습니다")),
            ).into_response()
        }
        Err(err) => {
            tracing::error!("사용자 수정 실패: {}", err);
            (
                StatusCode::INTERNAL_SERVER_ERROR,
                Json(ErrorResponse::new("update_error", "사용자 정보 수정에 실패했습니다")),
            ).into_response()
        }
    }
}

// 사용자 삭제 핸들러 (관리자 전용)
pub async fn delete_user(
    State(state): State<AppState>,
    Path(user_id): Path<i32>,
    Extension(claims): Extension<Claims>,
) -> impl IntoResponse {
    // 관리자 권한 확인 (미들웨어에서 이미 확인하지만 추가 보안)
    if claims.role != "admin" {
        return (
            StatusCode::FORBIDDEN,
            Json(ErrorResponse::new("permission_denied", "관리자 권한이 필요합니다")),
        ).into_response();
    }

    // 자기 자신은 삭제할 수 없음
    let current_user_id: i32 = claims.sub.parse().unwrap_or(0);
    if current_user_id == user_id {
        return (
            StatusCode::BAD_REQUEST,
            Json(ErrorResponse::new("self_delete_error", "자기 자신은 삭제할 수 없습니다")),
        ).into_response();
    }

    let user_service = UserService::new(state.db_pool);

    match user_service.delete_user(user_id).await {
        Ok(true) => {
            (StatusCode::NO_CONTENT, Json(())).into_response()
        }
        Ok(false) => {
            (
                StatusCode::NOT_FOUND,
                Json(ErrorResponse::new("user_not_found", "사용자를 찾을 수 없습니다")),
            ).into_response()
        }
        Err(err) => {
            tracing::error!("사용자 삭제 실패: {}", err);
            (
                StatusCode::INTERNAL_SERVER_ERROR,
                Json(ErrorResponse::new("delete_error", "사용자 삭제에 실패했습니다")),
            ).into_response()
        }
    }
} 