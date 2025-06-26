use crate::models::{User, RegisterRequest, UpdateUserRequest};
use anyhow::Result;
use bcrypt::{hash, verify, DEFAULT_COST};
use chrono::Utc;
use sqlx::PgPool;

pub struct UserService {
    pool: PgPool,
}

impl UserService {
    pub fn new(pool: PgPool) -> Self {
        Self { pool }
    }

    // 사용자 생성 (회원가입)
    pub async fn create_user(&self, request: RegisterRequest) -> Result<User> {
        // 비밀번호 해싱
        let hashed_password = hash(request.password, DEFAULT_COST)?;
        
        // 기본 역할 설정
        let role = request.role.unwrap_or_else(|| "user".to_string());

        // 데이터베이스에 사용자 생성
        let user = sqlx::query_as::<_, User>(
            r#"
            INSERT INTO users (email, password, name, role, is_active, created_at, updated_at)
            VALUES ($1, $2, $3, $4, $5, $6, $7)
            RETURNING id, email, password, name, role, is_active, created_at, updated_at
            "#,
        )
        .bind(&request.email)
        .bind(&hashed_password)
        .bind(&request.name)
        .bind(&role)
        .bind(true)
        .bind(Utc::now())
        .bind(Utc::now())
        .fetch_one(&self.pool)
        .await?;

        tracing::info!("새 사용자 생성 완료: {}", user.email);
        Ok(user)
    }

    // 이메일로 사용자 조회
    pub async fn find_by_email(&self, email: &str) -> Result<Option<User>> {
        let user = sqlx::query_as::<_, User>(
            "SELECT id, email, password, name, role, is_active, created_at, updated_at FROM users WHERE email = $1"
        )
        .bind(email)
        .fetch_optional(&self.pool)
        .await?;

        Ok(user)
    }

    // ID로 사용자 조회
    pub async fn find_by_id(&self, id: i32) -> Result<Option<User>> {
        let user = sqlx::query_as::<_, User>(
            "SELECT id, email, password, name, role, is_active, created_at, updated_at FROM users WHERE id = $1"
        )
        .bind(id)
        .fetch_optional(&self.pool)
        .await?;

        Ok(user)
    }

    // 모든 사용자 조회
    pub async fn find_all(&self) -> Result<Vec<User>> {
        let users = sqlx::query_as::<_, User>(
            "SELECT id, email, password, name, role, is_active, created_at, updated_at FROM users ORDER BY created_at DESC"
        )
        .fetch_all(&self.pool)
        .await?;

        Ok(users)
    }

    // 사용자 정보 수정
    pub async fn update_user(&self, id: i32, request: UpdateUserRequest) -> Result<Option<User>> {
        let mut query = "UPDATE users SET updated_at = NOW()".to_string();
        let mut params: Vec<String> = vec![];
        let mut param_count = 1;

        // 동적 쿼리 생성
        if let Some(email) = &request.email {
            query.push_str(&format!(", email = ${}", param_count));
            params.push(email.clone());
            param_count += 1;
        }

        if let Some(password) = &request.password {
            let hashed_password = hash(password, DEFAULT_COST)?;
            query.push_str(&format!(", password = ${}", param_count));
            params.push(hashed_password);
            param_count += 1;
        }

        if let Some(name) = &request.name {
            query.push_str(&format!(", name = ${}", param_count));
            params.push(name.clone());
            param_count += 1;
        }

        if let Some(role) = &request.role {
            query.push_str(&format!(", role = ${}", param_count));
            params.push(role.clone());
            param_count += 1;
        }

        if let Some(is_active) = request.is_active {
            query.push_str(&format!(", is_active = ${}", param_count));
            params.push(is_active.to_string());
            param_count += 1;
        }

        query.push_str(&format!(
            " WHERE id = ${} RETURNING id, email, password, name, role, is_active, created_at, updated_at",
            param_count
        ));

        // 쿼리 실행
        let mut sql_query = sqlx::query_as::<_, User>(&query);
        
        for param in params {
            sql_query = sql_query.bind(param);
        }
        sql_query = sql_query.bind(id);

        let user = sql_query.fetch_optional(&self.pool).await?;

        if user.is_some() {
            tracing::info!("사용자 정보 수정 완료: ID {}", id);
        }

        Ok(user)
    }

    // 사용자 삭제
    pub async fn delete_user(&self, id: i32) -> Result<bool> {
        let result = sqlx::query("DELETE FROM users WHERE id = $1")
            .bind(id)
            .execute(&self.pool)
            .await?;

        let deleted = result.rows_affected() > 0;
        
        if deleted {
            tracing::info!("사용자 삭제 완료: ID {}", id);
        }

        Ok(deleted)
    }

    // 비밀번호 검증
    pub fn verify_password(&self, password: &str, hashed_password: &str) -> Result<bool> {
        Ok(verify(password, hashed_password)?)
    }
} 