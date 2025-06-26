use chrono::{DateTime, Utc};
use serde::{Deserialize, Serialize};
use sqlx::FromRow;
use validator::Validate;

// 사용자 모델 (데이터베이스 테이블과 매핑)
#[derive(Debug, Clone, FromRow, Serialize)]
pub struct User {
    pub id: i32,
    pub email: String,
    #[serde(skip_serializing)] // 비밀번호는 직렬화에서 제외
    pub password: String,
    pub name: String,
    pub role: String,
    pub is_active: bool,
    pub created_at: DateTime<Utc>,
    pub updated_at: DateTime<Utc>,
}

// 사용자 응답 구조체 (비밀번호 제외)
#[derive(Debug, Serialize)]
pub struct UserResponse {
    pub id: i32,
    pub email: String,
    pub name: String,
    pub role: String,
    pub is_active: bool,
    pub created_at: DateTime<Utc>,
    pub updated_at: DateTime<Utc>,
}

impl From<User> for UserResponse {
    fn from(user: User) -> Self {
        UserResponse {
            id: user.id,
            email: user.email,
            name: user.name,
            role: user.role,
            is_active: user.is_active,
            created_at: user.created_at,
            updated_at: user.updated_at,
        }
    }
}

// 회원가입 요청 구조체
#[derive(Debug, Deserialize, Validate)]
pub struct RegisterRequest {
    #[validate(email(message = "유효한 이메일 주소를 입력해주세요"))]
    pub email: String,
    
    #[validate(length(min = 6, message = "비밀번호는 최소 6자 이상이어야 합니다"))]
    pub password: String,
    
    #[validate(length(min = 2, max = 50, message = "이름은 2자 이상 50자 이하여야 합니다"))]
    pub name: String,
    
    #[validate(custom = "validate_role")]
    pub role: Option<String>,
}

// 로그인 요청 구조체
#[derive(Debug, Deserialize, Validate)]
pub struct LoginRequest {
    #[validate(email(message = "유효한 이메일 주소를 입력해주세요"))]
    pub email: String,
    
    #[validate(length(min = 1, message = "비밀번호를 입력해주세요"))]
    pub password: String,
}

// 사용자 정보 수정 요청 구조체
#[derive(Debug, Deserialize, Validate)]
pub struct UpdateUserRequest {
    #[validate(email(message = "유효한 이메일 주소를 입력해주세요"))]
    pub email: Option<String>,
    
    #[validate(length(min = 6, message = "비밀번호는 최소 6자 이상이어야 합니다"))]
    pub password: Option<String>,
    
    #[validate(length(min = 2, max = 50, message = "이름은 2자 이상 50자 이하여야 합니다"))]
    pub name: Option<String>,
    
    #[validate(custom = "validate_role")]
    pub role: Option<String>,
    
    pub is_active: Option<bool>,
}

// 인증 응답 구조체
#[derive(Debug, Serialize)]
pub struct AuthResponse {
    pub token: String,
    pub user: UserResponse,
}

// JWT 클레임 구조체
#[derive(Debug, Serialize, Deserialize)]
pub struct Claims {
    pub sub: String,    // 사용자 ID
    pub email: String,  // 이메일
    pub role: String,   // 역할
    pub exp: usize,     // 만료 시간
}

// 역할 유효성 검사 함수
fn validate_role(role: &str) -> Result<(), validator::ValidationError> {
    match role {
        "user" | "admin" => Ok(()),
        _ => {
            let mut error = validator::ValidationError::new("invalid_role");
            error.message = Some("역할은 'user' 또는 'admin'이어야 합니다".into());
            Err(error)
        }
    }
}

// 에러 응답 구조체
#[derive(Debug, Serialize)]
pub struct ErrorResponse {
    pub error: String,
    pub message: String,
}

impl ErrorResponse {
    pub fn new(error: &str, message: &str) -> Self {
        Self {
            error: error.to_string(),
            message: message.to_string(),
        }
    }
} 