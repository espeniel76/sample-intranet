from pydantic_settings import BaseSettings
from typing import Optional


class Settings(BaseSettings):
    """애플리케이션 설정"""
    
    # 데이터베이스 설정 (공유 데이터베이스로 변경)
    database_url: str = "postgresql://postgres:password@localhost:5432/sample_intranet"
    
    # JWT 설정
    jwt_secret: str = "your-jwt-secret-change-this-in-production"
    jwt_expires_hours: int = 24
    jwt_algorithm: str = "HS256"
    
    # 서버 설정
    app_name: str = "Sample Intranet Backend Python"
    debug: bool = True
    
    # CORS 설정
    cors_origins: list = ["*"]
    
    class Config:
        env_file = ".env"  # 환경 변수 파일 로드
        case_sensitive = False


# 설정 인스턴스 생성
settings = Settings() 