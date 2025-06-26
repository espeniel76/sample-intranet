# 🏢 Sample Intranet Project

**6개 언어**로 구현된 회원관리 인트라넷 시스템입니다. 각 백엔드는 동일한 API 스펙을 제공하며 같은 PostgreSQL 데이터베이스를 공유합니다.

## 📁 프로젝트 구조

```
sample-intranet/
├── backend-go/               # 🐹 Go 백엔드 (Gin + GORM)
├── backend-python/           # 🐍 Python 백엔드 (FastAPI + SQLAlchemy)
├── backend-java/             # ☕ Java 백엔드 (Spring Boot + JPA)
├── backend-nodejs/           # 🟢 Node.js 백엔드 (Express + Prisma)
├── backend-php/              # 🐘 PHP 백엔드 (Slim + PDO)
├── backend-rust/             # 🦀 Rust 백엔드 (Axum + SQLx)
├── frontend/                 # 🌐 프론트엔드 (빈 폴더)
├── docker-compose.shared.yml # 공유 데이터베이스 설정
└── README.md                # 이 파일
```

## 🗄️ 공유 데이터베이스 구조

**6개 백엔드 모두 동일한 PostgreSQL 데이터베이스**를 사용합니다:

- **데이터베이스명**: `sample_intranet`
- **포트**: `5432`
- **사용자**: `postgres` / `password`
- **테이블**: `users` (동일한 스키마 사용)

## 🚀 실행 방법

### 1️⃣ 공유 데이터베이스만 실행
```bash
# 공유 PostgreSQL 데이터베이스만 시작
docker-compose -f docker-compose.shared.yml up -d
```

### 2️⃣ Go 백엔드 실행
```bash
cd backend-go
docker-compose up --build

# 또는 로컬 실행
go run main.go
```

### 3️⃣ Python 백엔드 실행
```bash
cd backend-python
docker-compose up --build

# 또는 로컬 실행 (가상환경 권장)
python -m venv venv
source venv/bin/activate  # Windows: venv\Scripts\activate
pip install -r requirements.txt
python main.py
```

### 4️⃣ Java 백엔드 실행
```bash
cd backend-java
docker-compose up --build

# 또는 로컬 실행
mvn spring-boot:run
```

### 5️⃣ Node.js 백엔드 실행
```bash
cd backend-nodejs
docker-compose up --build

# 또는 로컬 실행
npm install
npm run dev
```

### 6️⃣ PHP 백엔드 실행
```bash
cd backend-php
docker-compose up --build

# 또는 로컬 실행
composer install
composer start
```

### 7️⃣ Rust 백엔드 실행
```bash
cd backend-rust
docker-compose up --build

# 또는 로컬 실행
cargo run
```

### 8️⃣ 모든 백엔드 동시 실행 (권장)
```bash
# 1. 공유 데이터베이스 시작
docker-compose -f docker-compose.shared.yml up -d

# 2. 각 백엔드를 별도 터미널에서 실행
cd backend-go && docker-compose up app --build
cd backend-python && docker-compose up app --build
cd backend-java && docker-compose up app --build
cd backend-nodejs && docker-compose up app --build
cd backend-php && docker-compose up app --build
cd backend-rust && docker-compose up app --build
```

## 🔗 접속 정보

| 서비스 | URL | 포트 |
|--------|-----|------|
| **Go API** | http://localhost:8080 | 8080 |
| **Python API** | http://localhost:8000 | 8000 |
| **Java API** | http://localhost:9090 | 9090 |
| **Node.js API** | http://localhost:3000 | 3000 |
| **PHP API** | http://localhost:8090 | 8090 |
| **Rust API** | http://localhost:8070 | 8070 |
| **API 문서 (Python)** | http://localhost:8000/docs | - |
| **PostgreSQL** | localhost:5432 | 5432 |

## 🎯 주요 API 엔드포인트

### **공통 엔드포인트 (6개 언어 모두 동일)**
| 기능 | Go (8080) | Python (8000) | Java (9090) | Node.js (3000) | PHP (8090) | Rust (8070) |
|------|-----------|---------------|-------------|---------------|------------|-------------|
| 헬스체크 | `GET /health` | `GET /health` | `GET /api/v1/health` | `GET /api/v1/health` | `GET /health` | `GET /health` |
| 회원가입 | `POST /api/v1/auth/register` | `POST /api/v1/auth/register` | `POST /api/v1/auth/register` | `POST /api/v1/auth/register` | `POST /api/v1/auth/register` | `POST /api/v1/auth/register` |
| 로그인 | `POST /api/v1/auth/login` | `POST /api/v1/auth/login` | `POST /api/v1/auth/login` | `POST /api/v1/auth/login` | `POST /api/v1/auth/login` | `POST /api/v1/auth/login` |
| 사용자 목록 | `GET /api/v1/users` | `GET /api/v1/users` | `GET /api/v1/users` | `GET /api/v1/users` | `GET /api/v1/users` | `GET /api/v1/users` |
| 사용자 조회 | `GET /api/v1/users/{id}` | `GET /api/v1/users/{id}` | `GET /api/v1/users/{id}` | `GET /api/v1/users/{id}` | `GET /api/v1/users/{id}` | `GET /api/v1/users/{id}` |
| 사용자 수정 | `PUT /api/v1/users/{id}` | `PUT /api/v1/users/{id}` | `PUT /api/v1/users/{id}` | `PUT /api/v1/users/{id}` | `PUT /api/v1/users/{id}` | `PUT /api/v1/users/{id}` |
| 사용자 삭제 | `DELETE /api/v1/admin/users/{id}` | `DELETE /api/v1/admin/users/{id}` | `DELETE /api/v1/admin/users/{id}` | `DELETE /api/v1/admin/users/{id}` | `DELETE /api/v1/admin/users/{id}` | `DELETE /api/v1/admin/users/{id}` |

## 🔄 API 호환성

**6개 백엔드는 모두 동일한 API 스펙**을 제공하므로 서로 교체 가능합니다:

```bash
# Go 백엔드 사용
curl -X POST http://localhost:8080/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123","name":"테스트"}'

# Python 백엔드 사용 (동일한 요청)
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123","name":"테스트"}'

# Java 백엔드 사용 (동일한 요청)
curl -X POST http://localhost:9090/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123","name":"테스트"}'

# Node.js 백엔드 사용 (동일한 요청)
curl -X POST http://localhost:3000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123","name":"테스트"}'

# PHP 백엔드 사용 (동일한 요청)
curl -X POST http://localhost:8090/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123","name":"테스트"}'

# Rust 백엔드 사용 (동일한 요청)
curl -X POST http://localhost:8070/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123","name":"테스트"}'
```

## 🆚 백엔드 비교

| 특징 | Go (Gin) | Python (FastAPI) | Java (Spring Boot) | Node.js (Express) | PHP (Slim) | **Rust (Axum)** |
|------|----------|------------------|--------------------|------------------|------------|------------------|
| **성능** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| **개발 속도** | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ |
| **메모리 사용** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| **메모리 안전성** | ⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| **API 문서** | 수동 | 자동 생성 | 수동 | 수동 | 수동 | 수동 |
| **타입 안전성** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| **엔터프라이즈** | ⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐ |
| **생태계** | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ |
| **학습 곡선** | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐ |
| **호스팅 비용** | ⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ |
| **동시성 처리** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ |

## 🐳 Docker 네트워킹

모든 서비스는 `shared_network`로 연결되어 서로 통신할 수 있습니다:

```yaml
networks:
  shared_network:
    driver: bridge
```

## 📊 데이터베이스 스키마

```sql
-- 공통 사용자 테이블 (6개 백엔드 모두 동일)
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,  -- bcrypt 해시
    name VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'user', -- 'user' | 'admin'
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);
```

## 🛠️ 각 백엔드 기술 스택

### 🐹 Go 백엔드
- **프레임워크**: Gin Web Framework
- **ORM**: GORM
- **특징**: 높은 성능, 낮은 메모리 사용량

### 🐍 Python 백엔드
- **프레임워크**: FastAPI
- **ORM**: SQLAlchemy
- **특징**: 자동 API 문서 생성, 빠른 개발

### ☕ Java 백엔드
- **프레임워크**: Spring Boot 3.2
- **ORM**: Spring Data JPA (Hibernate)
- **특징**: 엔터프라이즈급 기능, 강력한 생태계

### 🟢 Node.js 백엔드
- **언어**: TypeScript
- **프레임워크**: Express.js
- **ORM**: Prisma
- **특징**: 뛰어난 비동기 처리, 풍부한 npm 생태계

### 🐘 PHP 백엔드
- **언어**: PHP 8.2
- **프레임워크**: Slim Framework 4
- **Database**: PDO with PostgreSQL
- **특징**: 웹 개발 특화, 저렴한 호스팅, Clean Architecture

### 🦀 Rust 백엔드
- **언어**: Rust 1.75+
- **프레임워크**: Axum
- **ORM**: SQLx
- **특징**: 최고 성능, 메모리 안전성, 제로 코스트 추상화

## 🛠️ 개발 팁

### **로컬 개발 시**
```bash
# 1. 공유 DB만 Docker로 실행
docker-compose -f docker-compose.shared.yml up -d

# 2. 각 백엔드는 로컬에서 실행 (빠른 개발)
cd backend-go && go run main.go
cd backend-python && python main.py
cd backend-java && mvn spring-boot:run
cd backend-nodejs && npm run dev
cd backend-php && composer start
```

### **운영 배포 시**
```bash
# 모든 서비스를 Docker로 실행
docker-compose -f docker-compose.shared.yml up -d
cd backend-go && docker-compose up -d
cd backend-python && docker-compose up -d
cd backend-java && docker-compose up -d
cd backend-nodejs && docker-compose up -d
cd backend-php && docker-compose up -d
```

### **성능 테스트**
```bash
# 각 백엔드의 성능 비교 테스트
ab -n 1000 -c 10 http://localhost:8080/health    # Go
ab -n 1000 -c 10 http://localhost:8000/health    # Python
ab -n 1000 -c 10 http://localhost:9090/api/v1/health  # Java
ab -n 1000 -c 10 http://localhost:3000/api/v1/health  # Node.js
ab -n 1000 -c 10 http://localhost:8090/health    # PHP
```

## 🔧 설정 커스터마이징

### **Go 백엔드**
- `backend-go/config/config.yaml` 수정
- 환경 변수: `DATABASE_HOST`, `DATABASE_PORT` 등

### **Python 백엔드**
- `.env` 파일 생성 또는 환경 변수 설정
- `DATABASE_URL`, `JWT_SECRET` 등

### **Java 백엔드**
- `backend-java/src/main/resources/application.yml` 수정
- 환경 변수: `SPRING_DATASOURCE_URL`, `JWT_SECRET` 등

### **Node.js 백엔드**
- `.env` 파일 생성 또는 환경 변수 설정
- `DATABASE_URL`, `JWT_SECRET`, `NODE_ENV` 등

### **PHP 백엔드**
- `.env` 파일 생성 또는 환경 변수 설정
- `DB_HOST`, `JWT_SECRET`, `APP_DEBUG` 등

### **Rust 백엔드**
- `Cargo.toml` 수정
- 환경 변수: `DATABASE_URL`, `JWT_SECRET` 등

## 📈 사용 사례별 추천

| 용도 | 추천 백엔드 | 이유 |
|------|-------------|------|
| **스타트업 MVP** | Python, PHP 또는 Node.js | 빠른 개발 속도 |
| **대규모 서비스** | Rust 또는 Go | 최고 성능과 메모리 효율성 |
| **엔터프라이즈** | Java | 풍부한 엔터프라이즈 기능 |
| **실시간 기능** | Node.js 또는 Rust | 뛰어난 비동기 처리 |
| **마이크로서비스** | Rust 또는 Go | 작은 바이너리 크기, 빠른 시작 |
| **머신러닝 연동** | Python | ML 라이브러리 생태계 |
| **웹 에이전시** | PHP | 저렴한 호스팅, 웹 개발 특화 |
| **소규모 웹사이트** | PHP | 간편한 배포, 낮은 진입장벽 |
| **시스템 프로그래밍** | Rust | 메모리 안전성, C/C++ 수준 성능 |
| **금융/보안** | Rust 또는 Java | 높은 안전성과 신뢰성 |

## 🤝 기여하기

1. 이슈 리포트: 버그나 개선사항을 Issues에 등록
2. 기능 제안: 새로운 기능 아이디어 공유
3. 코드 기여: Pull Request 환영
4. 문서 개선: README나 API 문서 개선
5. 성능 최적화: 각 언어별 성능 개선 제안

## 📄 라이선스

이 프로젝트는 MIT 라이선스 하에 배포됩니다.

---

**💡 Tip**: 
- **프로토타이핑**: Python, PHP 또는 Node.js
- **운영 성능**: Rust 또는 Go  
- **웹 에이전시**: PHP (저렴한 호스팅)
- **팀 선호도**: 팀이 가장 익숙한 언어 선택
- **하이브리드**: 상황에 따라 다른 백엔드 조합 사용 가능!
- **최고 성능이 필요한 경우**: Rust 선택 (메모리 안전성 + C++ 수준 성능) 