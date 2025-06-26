# 🦀 Rust 백엔드 (Axum + SQLx)

**Rust**와 **Axum** 프레임워크로 구현된 고성능 백엔드 API 서버입니다.

## 🚀 주요 특징

- **⚡ 높은 성능**: Rust의 제로 코스트 추상화와 메모리 안전성
- **🔥 Axum 프레임워크**: 모던하고 ergonomic한 웹 프레임워크  
- **💾 SQLx**: 컴파일 타임 SQL 검증이 가능한 async ORM
- **🔐 JWT 인증**: 안전한 토큰 기반 인증
- **📊 구조화된 로깅**: tracing 크레이트를 사용한 고급 로깅
- **🐳 Docker 지원**: 멀티 스테이지 빌드로 최적화된 컨테이너

## 🏗️ 아키텍처

```
src/
├── main.rs          # 애플리케이션 진입점
├── config.rs        # 설정 관리
├── database.rs      # 데이터베이스 연결 및 마이그레이션
├── models.rs        # 데이터 모델 및 스키마
├── services.rs      # 비즈니스 로직
├── middleware.rs    # JWT 인증 미들웨어
└── handlers/        # HTTP 요청 핸들러
    ├── mod.rs
    ├── auth.rs      # 인증 관련 (회원가입, 로그인)
    └── users.rs     # 사용자 관리
```

## 🛠️ 기술 스택

| 카테고리 | 기술 | 버전 |
|----------|------|------|
| **언어** | Rust | 1.75+ |
| **웹 프레임워크** | Axum | 0.7 |
| **비동기 런타임** | Tokio | 1.0 |
| **데이터베이스** | PostgreSQL | 15 |
| **ORM** | SQLx | 0.7 |
| **인증** | JWT | jsonwebtoken 9.2 |
| **로깅** | tracing | 0.1 |
| **비밀번호 해싱** | bcrypt | 0.15 |

## 🚀 빠른 시작

### 1️⃣ 로컬 실행

```bash
# 의존성 설치 및 빌드
cargo build

# 환경 변수 설정
cp .env.example .env

# 데이터베이스 시작 (Docker 사용)
docker-compose -f ../docker-compose.shared.yml up -d postgres

# 개발 모드로 실행
cargo run
```

### 2️⃣ Docker로 실행

```bash
# 공유 네트워크 생성 (처음 한 번만)
docker network create shared_network

# Docker Compose로 실행
docker-compose up --build
```

## 🔗 API 엔드포인트

| 기능 | 메서드 | 엔드포인트 | 인증 필요 |
|------|--------|------------|-----------|
| **헬스체크** | GET | `/health` | ❌ |
| **회원가입** | POST | `/api/v1/auth/register` | ❌ |
| **로그인** | POST | `/api/v1/auth/login` | ❌ |
| **사용자 목록** | GET | `/api/v1/users` | ✅ |
| **사용자 조회** | GET | `/api/v1/users/{id}` | ✅ |
| **사용자 수정** | PUT | `/api/v1/users/{id}` | ✅ |
| **사용자 삭제** | DELETE | `/api/v1/admin/users/{id}` | ✅ (관리자) |

## 📝 API 사용 예제

### 회원가입
```bash
curl -X POST http://localhost:8070/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password123",
    "name": "홍길동"
  }'
```

### 로그인
```bash
curl -X POST http://localhost:8070/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password123"
  }'
```

### 사용자 목록 조회
```bash
curl -X GET http://localhost:8070/api/v1/users \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

## ⚙️ 환경 변수

| 변수명 | 설명 | 기본값 |
|--------|------|--------|
| `DATABASE_URL` | PostgreSQL 연결 URL | `postgresql://postgres:password@localhost:5432/sample_intranet` |
| `SERVER_ADDRESS` | 서버 바인딩 주소 | `0.0.0.0:8070` |
| `JWT_SECRET` | JWT 서명 키 | `your-secret-key-change-in-production` |
| `JWT_EXPIRES_HOURS` | JWT 만료 시간 (시간) | `24` |
| `RUST_LOG` | 로그 레벨 | `sample_intranet_rust=debug,tower_http=debug` |

## 🏭 운영 배포

### 성능 최적화된 빌드
```bash
# Release 모드로 빌드
cargo build --release

# 실행
./target/release/sample-intranet-rust
```

### Docker 프로덕션 배포
```bash
# 프로덕션용 환경 변수 설정
export JWT_SECRET="your-production-secret-key"
export DATABASE_URL="postgresql://user:pass@db:5432/sample_intranet"

# Docker Compose로 배포
docker-compose -f docker-compose.yml up -d
```

## 🔍 성능 특징

- **메모리 사용량**: ~5-10MB (기본 상태)
- **시작 시간**: ~100ms
- **처리량**: 50,000+ req/sec (단순 API 기준)
- **지연 시간**: 평균 <1ms

## 🧪 테스트

```bash
# 단위 테스트 실행
cargo test

# 통합 테스트 실행
cargo test --test integration

# 벤치마크 실행
cargo bench
```

## 🛡️ 보안 기능

- **JWT 토큰**: 상태 없는 인증
- **bcrypt 해싱**: 안전한 비밀번호 저장
- **CORS 설정**: 크로스 오리진 요청 제어
- **입력 검증**: validator 크레이트를 통한 엄격한 검증
- **SQL 인젝션 방지**: 매개변수화된 쿼리 사용

## 🐛 디버깅

### 로그 확인
```bash
# 상세 로그 활성화
export RUST_LOG=debug
cargo run

# 특정 모듈만 로그 확인
export RUST_LOG=sample_intranet_rust::handlers=debug
```

### 성능 분석
```bash
# CPU 프로파일링
cargo install flamegraph
cargo flamegraph --bin sample-intranet-rust

# 메모리 사용량 확인
valgrind --tool=massif ./target/release/sample-intranet-rust
```

## 🤝 기여하기

1. 이슈 리포트 환경
2. 기능 제안
3. 성능 최적화
4. 문서 개선

## 📄 라이선스

MIT License 