# 🐍 Sample Intranet Backend (Python)

FastAPI와 SQLAlchemy를 사용한 회원관리 백엔드 API 서버입니다.

## 🚀 기술 스택

- **Framework**: FastAPI
- **ORM**: SQLAlchemy
- **Database**: PostgreSQL
- **Authentication**: JWT + bcrypt
- **Validation**: Pydantic
- **Configuration**: Pydantic Settings
- **Logging**: Loguru
- **ASGI Server**: Uvicorn

## 📁 프로젝트 구조

```
backend-python/
├── main.py              # FastAPI 애플리케이션 진입점
├── config.py            # 설정 관리
├── database.py          # 데이터베이스 연결
├── models.py            # SQLAlchemy 모델
├── schemas.py           # Pydantic 스키마
├── auth.py              # JWT 인증 및 암호화
├── requirements.txt     # Python 의존성
├── Dockerfile          # 도커 이미지 설정
├── docker-compose.yml  # 도커 컴포즈 설정
└── README.md           # 이 파일
```

## 🔧 설정

### 1. 의존성 설치
```bash
# 가상환경 생성 (권장)
python -m venv venv
source venv/bin/activate  # Windows: venv\Scripts\activate

# 의존성 설치
pip install -r requirements.txt
```

### 2. 환경 변수 설정
`.env` 파일을 생성하고 다음 내용을 추가:
```env
DATABASE_URL=postgresql://postgres:password@localhost:5432/sample_intranet_python
JWT_SECRET=your-super-secret-jwt-key-change-this-in-production
JWT_EXPIRES_HOURS=24
```

### 3. 데이터베이스 설정
PostgreSQL 데이터베이스를 생성:
```sql
CREATE DATABASE sample_intranet_python;
```

## 🏃‍♂️ 실행 방법

### 로컬 개발 환경
```bash
# 직접 실행
python main.py

# 또는 uvicorn으로 실행
uvicorn main:app --reload --host 0.0.0.0 --port 8000
```

### Docker 실행
```bash
# 전체 시스템 실행 (PostgreSQL + 백엔드)
docker-compose up --build

# 백그라운드 실행
docker-compose up -d
```

## 🎯 API 엔드포인트

### **헬스체크**
- `GET /health` - 서버 상태 확인

### **인증 (공개)**
- `POST /api/v1/auth/register` - 회원가입
- `POST /api/v1/auth/login` - 로그인

### **사용자 관리 (인증 필요)**
- `GET /api/v1/users` - 사용자 목록 조회
- `GET /api/v1/users/{id}` - 특정 사용자 조회
- `PUT /api/v1/users/{id}` - 사용자 정보 수정

### **관리자 전용**
- `DELETE /api/v1/admin/users/{id}` - 사용자 삭제

## 📚 API 문서

서버 실행 후 다음 URL에서 자동 생성된 API 문서를 확인할 수 있습니다:
- **Swagger UI**: http://localhost:8000/docs
- **ReDoc**: http://localhost:8000/redoc

## 🔗 API 사용 예시

### 회원가입
```bash
curl -X POST "http://localhost:8000/api/v1/auth/register" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123",
    "name": "테스트 사용자",
    "role": "user"
  }'
```

### 로그인
```bash
curl -X POST "http://localhost:8000/api/v1/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

### 사용자 목록 조회 (인증 토큰 필요)
```bash
curl -X GET "http://localhost:8000/api/v1/users" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

## 🐳 Docker 설정

### 포트 매핑
- **FastAPI**: 8000 포트
- **PostgreSQL**: 5433 포트 (Go 백엔드와 충돌 방지)

### 주요 차이점
| 구분 | Go 백엔드 | Python 백엔드 |
|------|-----------|---------------|
| 포트 | 8080 | 8000 |
| DB 포트 | 5432 | 5433 |
| DB 이름 | sample_intranet | sample_intranet_python |
| 컨테이너명 | sample_intranet_* | sample_intranet_*_python |

## 🛠️ 개발 도구

### 코드 품질
```bash
# 코드 포맷팅
black .

# 린팅
flake8 .

# 타입 체킹
mypy .
```

### 테스트
```bash
# 테스트 실행
pytest

# 테스트 커버리지
pytest --cov=.
```

## 🔒 보안 기능

- **JWT 토큰** 기반 인증
- **bcrypt**로 패스워드 해싱
- **역할 기반 접근 제어** (RBAC)
- **CORS** 설정
- **SQL 인젝션** 방지 (SQLAlchemy ORM)
- **데이터 유효성 검증** (Pydantic)

## 📝 주의사항

1. **운영 환경**에서는 `JWT_SECRET`을 강력한 키로 변경하세요
2. **데이터베이스 연결 정보**를 환경 변수로 관리하세요
3. **DEBUG 모드**를 운영에서는 비활성화하세요
4. **CORS 설정**을 운영 환경에 맞게 조정하세요

## 🆚 Go vs Python 백엔드 비교

| 특징 | Go 백엔드 | Python 백엔드 |
|------|-----------|---------------|
| **성능** | 더 빠름 | 충분히 빠름 |
| **개발 속도** | 보통 | 매우 빠름 |
| **가독성** | 좋음 | 매우 좋음 |
| **메모리 사용** | 적음 | 보통 |
| **타입 안정성** | 컴파일 타임 | 런타임 (Pydantic) |
| **패키지 생태계** | 좋음 | 매우 풍부 |

## 🤝 기여하기

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 라이선스

이 프로젝트는 MIT 라이선스 하에 배포됩니다. 