-- PostgreSQL 데이터베이스 설정 스크립트

-- 1. 데이터베이스 생성 (psql에서 실행)
CREATE DATABASE sample_intranet;

-- 2. 데이터베이스 연결 확인
\c sample_intranet;

-- 3. 사용자 테이블이 자동으로 생성됩니다 (GORM 마이그레이션)
-- 애플리케이션 실행 시 자동으로 다음과 같은 테이블이 생성됩니다:
-- CREATE TABLE users (
--     id SERIAL PRIMARY KEY,
--     email VARCHAR(255) UNIQUE NOT NULL,
--     password VARCHAR(255) NOT NULL,
--     name VARCHAR(255) NOT NULL,
--     role VARCHAR(50) DEFAULT 'user',
--     is_active BOOLEAN DEFAULT true,
--     created_at TIMESTAMP DEFAULT NOW(),
--     updated_at TIMESTAMP DEFAULT NOW(),
--     deleted_at TIMESTAMP
-- ); 