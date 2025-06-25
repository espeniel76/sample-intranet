from fastapi import FastAPI, HTTPException, Depends, status
from fastapi.middleware.cors import CORSMiddleware
from sqlalchemy.orm import Session
from sqlalchemy.exc import IntegrityError
from datetime import timedelta
import time

# 내부 모듈 임포트
from config import settings
from database import get_db, init_db
from models import User, UserRole
from schemas import (
    UserCreate, UserLogin, UserResponse, UserUpdate, 
    Token, MessageResponse, ErrorResponse
)
from auth import (
    hash_password, authenticate_user, create_access_token,
    get_current_user, get_current_admin_user
)
from loguru import logger

# FastAPI 앱 생성
app = FastAPI(
    title=settings.app_name,
    description="Python FastAPI 기반 회원관리 백엔드 API",
    version="1.0.0",
    debug=settings.debug
)

# CORS 미들웨어 설정
app.add_middleware(
    CORSMiddleware,
    allow_origins=settings.cors_origins,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)


@app.on_event("startup")
async def startup_event():
    """애플리케이션 시작 시 실행"""
    logger.info("FastAPI 애플리케이션 시작")
    try:
        # 데이터베이스 초기화
        init_db()
        logger.info("데이터베이스 초기화 완료")
    except Exception as e:
        logger.error(f"시작 시 오류 발생: {str(e)}")
        raise


# 헬스체크 엔드포인트
@app.get("/health", response_model=dict, tags=["Health"])
async def health_check():
    """서버 상태 확인"""
    return {
        "status": "healthy",
        "timestamp": int(time.time()),
        "app_name": settings.app_name
    }


# 인증 관련 엔드포인트들
@app.post("/api/v1/auth/register", response_model=UserResponse, tags=["Auth"])
async def register(user_data: UserCreate, db: Session = Depends(get_db)):
    """회원가입"""
    try:
        # 이메일 중복 확인
        existing_user = db.query(User).filter(User.email == user_data.email).first()
        if existing_user:
            raise HTTPException(
                status_code=status.HTTP_400_BAD_REQUEST,
                detail="이미 존재하는 이메일입니다"
            )
        
        # 비밀번호 해싱
        hashed_password = hash_password(user_data.password)
        
        # 새 사용자 생성
        db_user = User(
            email=user_data.email,
            password=hashed_password,
            name=user_data.name,
            role=user_data.role,
            is_active=user_data.is_active
        )
        
        db.add(db_user)
        db.commit()
        db.refresh(db_user)
        
        logger.info(f"새 사용자 등록: {user_data.email}")
        return db_user
        
    except IntegrityError:
        db.rollback()
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="데이터 무결성 오류"
        )
    except Exception as e:
        db.rollback()
        logger.error(f"회원가입 오류: {str(e)}")
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail="회원가입 처리 중 오류가 발생했습니다"
        )


@app.post("/api/v1/auth/login", response_model=Token, tags=["Auth"])
async def login(user_credentials: UserLogin, db: Session = Depends(get_db)):
    """로그인"""
    # 사용자 인증
    user = authenticate_user(db, user_credentials.email, user_credentials.password)
    if not user:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="이메일 또는 비밀번호가 올바르지 않습니다"
        )
    
    # 액세스 토큰 생성
    access_token = create_access_token(data={"sub": str(user.id)})
    
    logger.info(f"사용자 로그인: {user.email}")
    
    return {
        "access_token": access_token,
        "token_type": "bearer",
        "expires_in": settings.jwt_expires_hours * 3600,  # 초 단위
        "user": user
    }


# 사용자 관리 엔드포인트들
@app.get("/api/v1/users", response_model=list[UserResponse], tags=["Users"])
async def get_users(
    skip: int = 0,
    limit: int = 100,
    current_user: User = Depends(get_current_user),
    db: Session = Depends(get_db)
):
    """사용자 목록 조회 (인증 필요)"""
    users = db.query(User).offset(skip).limit(limit).all()
    return users


@app.get("/api/v1/users/{user_id}", response_model=UserResponse, tags=["Users"])
async def get_user(
    user_id: int,
    current_user: User = Depends(get_current_user),
    db: Session = Depends(get_db)
):
    """특정 사용자 조회"""
    user = db.query(User).filter(User.id == user_id).first()
    if not user:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="사용자를 찾을 수 없습니다"
        )
    return user


@app.put("/api/v1/users/{user_id}", response_model=UserResponse, tags=["Users"])
async def update_user(
    user_id: int,
    user_update: UserUpdate,
    current_user: User = Depends(get_current_user),
    db: Session = Depends(get_db)
):
    """사용자 정보 수정"""
    # 본인 또는 관리자만 수정 가능
    if current_user.id != user_id and current_user.role != UserRole.ADMIN:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="권한이 없습니다"
        )
    
    user = db.query(User).filter(User.id == user_id).first()
    if not user:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="사용자를 찾을 수 없습니다"
        )
    
    # 업데이트할 필드만 수정
    update_data = user_update.dict(exclude_unset=True)
    for field, value in update_data.items():
        setattr(user, field, value)
    
    db.commit()
    db.refresh(user)
    
    logger.info(f"사용자 정보 업데이트: {user.email}")
    return user


@app.delete("/api/v1/admin/users/{user_id}", response_model=MessageResponse, tags=["Admin"])
async def delete_user(
    user_id: int,
    current_admin: User = Depends(get_current_admin_user),
    db: Session = Depends(get_db)
):
    """사용자 삭제 (관리자 전용)"""
    user = db.query(User).filter(User.id == user_id).first()
    if not user:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="사용자를 찾을 수 없습니다"
        )
    
    # 자기 자신은 삭제할 수 없음
    if user.id == current_admin.id:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="자기 자신은 삭제할 수 없습니다"
        )
    
    db.delete(user)
    db.commit()
    
    logger.info(f"사용자 삭제: {user.email}")
    return {"message": f"사용자 '{user.name}'가 삭제되었습니다"}


# 에러 핸들러
@app.exception_handler(404)
async def not_found_handler(request, exc):
    return {"error": "페이지를 찾을 수 없습니다", "detail": str(exc)}


@app.exception_handler(500)
async def internal_server_error_handler(request, exc):
    logger.error(f"내부 서버 오류: {str(exc)}")
    return {"error": "내부 서버 오류", "detail": "서버에서 오류가 발생했습니다"}


if __name__ == "__main__":
    import uvicorn
    uvicorn.run(
        "main:app", 
        host="0.0.0.0", 
        port=8000, 
        reload=settings.debug,
        log_level="info"
    ) 