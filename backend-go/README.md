# Sample Intranet Backend

Go와 Gin 프레임워크를 사용한 회원관리 백엔드 API 서버입니다.

## 기술 스택

- **Framework**: Gin
- **ORM**: GORM
- **Database**: PostgreSQL
- **Authentication**: JWT + Sessions
- **Validation**: validator/v10
- **Configuration**: Viper
- **Logging**: Logrus

## 프로젝트 구조

```
backend/
├── config/
│   └── config.yaml           # 설정 파일
├── internal/
│   ├── auth/                 # JWT 인증 관련
│   ├── config/               # 설정 관리
│   ├── database/             # 데이터베이스 연결
│   ├── handlers/             # HTTP 핸들러
│   ├── middleware/           # 미들웨어
│   ├── models/               # 데이터 모델
│   ├── repositories/         # 데이터 액세스 레이어
│   ├── routes/               # 라우팅
│   └── services/             # 비즈니스 로직
├── go.mod
├── go.sum
├── main.go                   # 애플리케이션 진입점
└── README.md
```

## 설정

### PostgreSQL 데이터베이스 설정

1. PostgreSQL 설치 및 실행
2. 데이터베이스 생성:
   ```sql
   CREATE DATABASE sample_intranet;
   ```

3. `config/config.yaml` 파일에서 데이터베이스 설정 수정:
   ```yaml
   database:
     host: localhost
     port: 5432
     user: postgres
     password: your_password
     dbname: sample_intranet
     sslmode: disable
   ```

## 실행

### 개발 환경

```bash
# 의존성 설치
go mod tidy

# 서버 실행
go run main.go
```

서버는 기본적으로 `:8080` 포트에서 실행됩니다.

## API 엔드포인트

### 인증 (공개)

- `POST /api/v1/auth/register` - 회원가입
- `POST /api/v1/auth/login` - 로그인

### 사용자 관리 (인증 필요)

- `GET /api/v1/users` - 사용자 목록 조회
- `GET /api/v1/users/:id` - 특정 사용자 조회
- `PUT /api/v1/users/:id` - 사용자 정보 수정

### 관리자 전용 (관리자 권한 필요)

- `DELETE /api/v1/admin/users/:id` - 사용자 삭제

### 헬스체크

- `GET /health` - 서버 상태 확인

## API 사용 예시

### 회원가입
```bash
curl -X POST http://localhost:8080/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password123",
    "name": "홍길동",
    "role": "user"
  }'
```

### 로그인
```bash
curl -X POST http://localhost:8080/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password123"
  }'
```

### 사용자 목록 조회 (인증 토큰 필요)
```bash
curl -X GET http://localhost:8080/api/v1/users \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

## 보안

- JWT 토큰을 사용한 인증
- 비밀번호는 bcrypt로 해시화하여 저장
- 역할 기반 접근 제어 (RBAC)
- CORS 설정

## 개발 시 주의사항

1. `config.yaml`의 JWT secret과 세션 secret을 프로덕션에서는 안전한 값으로 변경하세요.
2. 데이터베이스 연결 정보를 환경 변수로 관리하는 것을 권장합니다.
3. 로그 레벨을 적절히 조정하여 운영하세요. 