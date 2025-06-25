from pydantic import BaseModel, EmailStr, validator
from typing import Optional
from datetime import datetime
from models import UserRole


# 기본 스키마들
class UserBase(BaseModel):
    """사용자 기본 스키마"""
    email: EmailStr
    name: str
    role: Optional[UserRole] = UserRole.USER
    is_active: Optional[bool] = True


class UserCreate(UserBase):
    """사용자 생성용 스키마"""
    password: str
    
    @validator('password')
    def validate_password(cls, v):
        """비밀번호 검증"""
        if len(v) < 6:
            raise ValueError('비밀번호는 최소 6자 이상이어야 합니다')
        return v


class UserUpdate(BaseModel):
    """사용자 업데이트용 스키마"""
    name: Optional[str] = None
    role: Optional[UserRole] = None
    is_active: Optional[bool] = None


class UserResponse(UserBase):
    """사용자 응답용 스키마"""
    id: int
    created_at: datetime
    updated_at: Optional[datetime] = None
    
    class Config:
        from_attributes = True  # ORM 모델에서 데이터 가져오기


# 인증 관련 스키마들
class UserLogin(BaseModel):
    """로그인용 스키마"""
    email: EmailStr
    password: str


class Token(BaseModel):
    """토큰 응답 스키마"""
    access_token: str
    token_type: str = "bearer"
    expires_in: int
    user: UserResponse


class TokenData(BaseModel):
    """토큰 데이터 스키마"""
    user_id: Optional[int] = None


# API 응답 스키마들
class MessageResponse(BaseModel):
    """메시지 응답 스키마"""
    message: str


class ErrorResponse(BaseModel):
    """에러 응답 스키마"""
    error: str
    detail: Optional[str] = None 