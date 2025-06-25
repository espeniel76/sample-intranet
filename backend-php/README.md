# 🐘 Sample Intranet Backend (PHP)

PHP 8.2와 Slim Framework를 사용한 회원관리 백엔드 API 서버입니다.

## 🚀 기술 스택

- **Language**: PHP 8.2
- **Framework**: Slim Framework 4
- **Database**: PostgreSQL with PDO
- **Authentication**: JWT + bcrypt
- **Architecture**: Clean Architecture (Domain-Driven Design)
- **Package Manager**: Composer
- **Container**: Docker

## 📁 프로젝트 구조

```
backend-php/
├── public/
│   └── index.php                 # 애플리케이션 엔트리 포인트
├── src/
│   ├── Domain/
│   │   └── User/
│   │       ├── User.php          # 사용자 도메인 모델
│   │       └── UserRepository.php # 사용자 레포지토리 인터페이스
│   ├── Infrastructure/
│   │   ├── Database/
│   │   │   └── DatabaseConnection.php # 데이터베이스 연결
│   │   ├── User/
│   │   │   └── PdoUserRepository.php # PDO 사용자 레포지토리 구현
│   │   ├── Auth/
│   │   │   └── JwtService.php    # JWT 인증 서비스
│   │   └── Logger/
│   │       └── LoggerFactory.php # 로거 팩토리
│   └── Application/
│       ├── Services/
│       │   └── UserService.php   # 사용자 비즈니스 로직
│       ├── Controllers/
│       │   ├── UserController.php # 사용자 API 컨트롤러
│       │   └── HealthController.php # 헬스체크 컨트롤러
│       └── Middleware/
│           ├── AuthMiddleware.php # JWT 인증 미들웨어
│           ├── AdminMiddleware.php # 관리자 권한 미들웨어
│           └── CorsMiddleware.php # CORS 미들웨어
├── composer.json                 # Composer 설정
├── settings.php                  # 애플리케이션 설정
├── dependencies.php              # 의존성 주입 설정
├── middleware.php                # 미들웨어 설정
├── routes.php                    # 라우트 설정
├── Dockerfile                    # 도커 이미지 설정
├── docker-compose.yml            # 도커 컴포즈 설정
├── env.example                   # 환경 변수 예시
└── README.md                     # 이 파일
```

## 🔧 설정

### 1. 개발 환경 요구사항
- **PHP 8.2** 이상
- **Composer 2.0** 이상
- **PostgreSQL** (Docker 또는 로컬 설치)
- **PHP 확장**: pdo, pdo_pgsql, bcrypt

### 2. 의존성 설치
```bash
# Composer 의존성 설치
composer install

# 자동 로딩 최적화
composer dump-autoload --optimize
```

### 3. 환경 변수 설정
`.env` 파일을 생성하거나 환경 변수 설정:

```env
# 애플리케이션 설정
APP_DEBUG=false
LOG_LEVEL=info

# 데이터베이스 설정 (공유 데이터베이스)
DB_HOST=localhost
DB_PORT=5432
DB_NAME=sample_intranet
DB_USERNAME=postgres
DB_PASSWORD=password

# JWT 설정
JWT_SECRET=your-super-secret-jwt-key-change-this-in-production-php
JWT_EXPIRES_IN=86400

# CORS 설정
CORS_ORIGINS=*
```

## 🏃‍♂️ 실행 방법

### 로컬 개발 환경
```bash
# PHP 내장 서버로 실행
composer start

# 또는 직접 실행
php -S localhost:8090 -t public

# 코드 품질 검사
composer phpcs
composer phpstan

# 테스트 실행
composer test
```

### Docker 실행
```bash
# 전체 시스템 실행 (PostgreSQL + 백엔드)
docker-compose up --build

# 백그라운드 실행
docker-compose up -d

# 로그 확인
docker-compose logs -f app
```

## 🎯 API 엔드포인트

### **헬스체크**
- `GET /health` - 서버 상태 확인
- `GET /api/v1/health` - 서버 상태 확인

### **인증 (공개)**
- `POST /api/v1/auth/register` - 회원가입
- `POST /api/v1/auth/login` - 로그인

### **사용자 관리 (인증 필요)**
- `GET /api/v1/users` - 사용자 목록 조회
- `GET /api/v1/users/{id}` - 특정 사용자 조회
- `PUT /api/v1/users/{id}` - 사용자 정보 수정
- `GET /api/v1/users/search?name=검색어` - 사용자 검색

### **관리자 전용**
- `DELETE /api/v1/admin/users/{id}` - 사용자 삭제

## 🔗 API 사용 예시

### 회원가입
```bash
curl -X POST "http://localhost:8090/api/v1/auth/register" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123",
    "name": "테스트 사용자",
    "role": "USER"
  }'
```

### 로그인
```bash
curl -X POST "http://localhost:8090/api/v1/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

### 사용자 목록 조회 (인증 토큰 필요)
```bash
curl -X GET "http://localhost:8090/api/v1/users" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

## 🐳 Docker 설정

### 포트 매핑
- **PHP 애플리케이션**: 8090 포트
- **PostgreSQL**: 5432 포트 (공유)

### 환경 변수
- `DB_HOST`: 데이터베이스 호스트
- `DB_PORT`: 데이터베이스 포트
- `DB_NAME`: 데이터베이스명
- `JWT_SECRET`: JWT 비밀키
- `CORS_ORIGINS`: CORS 허용 오리진

## ⚡ 성능 최적화

### Composer 최적화
```bash
# 클래스맵 최적화
composer dump-autoload --optimize --classmap-authoritative

# 프로덕션 의존성만 설치
composer install --no-dev --optimize-autoloader
```

### PHP 설정 최적화
```ini
; php.ini 권장 설정
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

## 🛠️ 개발 도구

### Composer 스크립트
```bash
# 개발 서버 시작
composer start

# 코드 스타일 검사
composer phpcs

# 정적 분석
composer phpstan

# 테스트 실행
composer test
```

### 코드 품질 도구
- **PHP_CodeSniffer**: PSR-12 코딩 표준 준수
- **PHPStan**: 정적 분석 (레벨 8)
- **PHPUnit**: 단위 테스트

## 🏗️ 아키텍처 패턴

### Clean Architecture
```
Application Layer (Controllers, Services)
    ↓
Domain Layer (Entities, Repositories)
    ↓
Infrastructure Layer (Database, External APIs)
```

### 의존성 주입
- **PSR-11 Container**: 의존성 주입 컨테이너
- **Interface Segregation**: 인터페이스 분리 원칙
- **Dependency Inversion**: 의존성 역전 원칙

## 🔒 보안 기능

- **JWT 토큰** 기반 인증
- **bcrypt**로 패스워드 해싱 (cost 12)
- **PDO Prepared Statements**로 SQL 인젝션 방지
- **CORS** 설정
- **입력 값 검증** 및 이스케이프
- **HTTP 보안 헤더** 설정

## 🆚 다른 백엔드와 비교

| 특징 | Go | Python | Java | **PHP** | Node.js |
|------|----|---------| -------- | ------- | --------|
| **성능** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐ |
| **개발 속도** | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| **학습 곡선** | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| **생태계** | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| **호스팅 비용** | ⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ |
| **웹 개발 특화** | ⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ |

## 📊 프로젝트 특징

### ✅ 장점
- **빠른 개발**: PHP의 웹 개발 특화된 문법
- **저렴한 비용**: 대부분의 웹 호스팅 서비스 지원
- **풍부한 생태계**: Composer를 통한 방대한 패키지 저장소
- **Clean Architecture**: 유지보수가 쉬운 구조
- **타입 힌팅**: PHP 8.2의 강력한 타입 시스템

### ⚠️ 고려사항
- **성능**: Go나 Java 대비 상대적으로 낮은 성능
- **메모리 사용**: 요청당 메모리 사용량
- **멀티스레딩**: 제한적인 동시성 처리

## 📝 주의사항

1. **운영 환경**에서는 `JWT_SECRET`을 강력한 키로 변경하세요
2. **OPcache**를 활성화하여 성능을 최적화하세요
3. **CORS 설정**을 운영 환경에 맞게 제한하세요
4. **로그 레벨**을 운영에서는 적절히 설정하세요
5. **데이터베이스 연결 풀**을 설정하세요

## 🤝 기여하기

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 라이선스

이 프로젝트는 MIT 라이선스 하에 배포됩니다.

---

**💡 PHP 백엔드의 장점**: 웹 개발에 특화된 언어, 저렴한 호스팅 비용, 빠른 프로토타이핑! 