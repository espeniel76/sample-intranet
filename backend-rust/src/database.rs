use sqlx::{postgres::PgPoolOptions, PgPool};

// 데이터베이스 연결 풀 생성
pub async fn create_pool(database_url: &str) -> anyhow::Result<PgPool> {
    let pool = PgPoolOptions::new()
        .max_connections(20)
        .connect(database_url)
        .await?;
    
    tracing::info!("데이터베이스 연결 풀 생성 완료");
    Ok(pool)
}

// 데이터베이스 마이그레이션 실행
pub async fn run_migrations(pool: &PgPool) -> anyhow::Result<()> {
    // 사용자 테이블 생성 (다른 백엔드와 동일한 스키마)
    sqlx::query(
        r#"
        CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            name VARCHAR(255) NOT NULL,
            role VARCHAR(50) DEFAULT 'user',
            is_active BOOLEAN DEFAULT true,
            created_at TIMESTAMPTZ DEFAULT NOW(),
            updated_at TIMESTAMPTZ DEFAULT NOW()
        )
        "#,
    )
    .execute(pool)
    .await?;

    // updated_at 자동 업데이트 트리거 생성
    sqlx::query(
        r#"
        CREATE OR REPLACE FUNCTION update_updated_at_column()
        RETURNS TRIGGER AS $$
        BEGIN
            NEW.updated_at = NOW();
            RETURN NEW;
        END;
        $$ language 'plpgsql';
        "#,
    )
    .execute(pool)
    .await?;

    // 트리거 적용
    sqlx::query(
        r#"
        DROP TRIGGER IF EXISTS update_users_updated_at ON users;
        CREATE TRIGGER update_users_updated_at
            BEFORE UPDATE ON users
            FOR EACH ROW
            EXECUTE FUNCTION update_updated_at_column();
        "#,
    )
    .execute(pool)
    .await?;

    tracing::info!("데이터베이스 마이그레이션 완료");
    Ok(())
} 