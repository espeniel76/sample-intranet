# ☕ Sample Intranet Backend (Java)

Spring Boot와 JPA를 사용한 회원관리 백엔드 API 서버입니다.

## 🚀 기술 스택

- **Framework**: Spring Boot 3.2
- **ORM**: Spring Data JPA (Hibernate)
- **Database**: PostgreSQL
- **Authentication**: JWT + Spring Security
- **Validation**: Bean Validation
- **Build Tool**: Maven
- **Java Version**: 17

## 📁 프로젝트 구조

```
backend-java/
├── src/main/java/com/example/intranet/
│   ├── SampleIntranetApplication.java    # 메인 애플리케이션
│   ├── config/
│   │   └── SecurityConfig.java           # Spring Security 설정
│   ├── entity/
│   │   ├── User.java                     # 사용자 엔티티
│   │   └── UserRole.java                 # 사용자 역할 enum
│   ├── repository/
│   │   └── UserRepository.java           # JPA 레포지토리
│   ├── service/
│   │   └── UserService.java              # 비즈니스 로직
│   ├── controller/
│   │   └── UserController.java           # REST API 컨트롤러
│   ├── dto/
│   │   ├── UserCreateRequest.java        # 요청 DTO
│   │   ├── UserLoginRequest.java
│   │   ├── UserResponse.java             # 응답 DTO
│   │   └── JwtResponse.java
│   └── util/
│       └── JwtUtil.java                  # JWT 유틸리티
├── src/main/resources/
│   └── application.yml                   # 애플리케이션 설정
├── pom.xml                              # Maven 설정
├── Dockerfile                           # 도커 이미지 설정
├── docker-compose.yml                   # 도커 컴포즈 설정
└── README.md                           # 이 파일
```

## 🔧 설정

### 1. 개발 환경 요구사항
- **Java 17** 이상
- **Maven 3.8** 이상
- **PostgreSQL** (Docker 또는 로컬 설치)

### 2. 의존성 설치
```bash
# Maven 의존성 설치
mvn clean install

# 또는 래퍼 사용
./mvnw clean install  # Unix/Linux/Mac
mvnw.cmd clean install  # Windows
```

### 3. 환경 변수 설정
`application.yml`에서 설정하거나 환경 변수로 오버라이드:

```yaml
spring:
  datasource:
    url: jdbc:postgresql://localhost:5432/sample_intranet
    username: postgres
    password: password

jwt:
  secret: your-super-secret-jwt-key-change-this-in-production
  expiration: 86400000  # 24시간 (밀리초)
```

## 🏃‍♂️ 실행 방법

### 로컬 개발 환경
```bash
# Maven으로 실행
mvn spring-boot:run

# 또는 JAR 파일 빌드 후 실행
mvn clean package
java -jar target/sample-intranet-backend-1.0.0.jar
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
curl -X POST "http://localhost:9090/api/v1/auth/register" \
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
curl -X POST "http://localhost:9090/api/v1/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

### 사용자 목록 조회 (인증 토큰 필요)
```bash
curl -X GET "http://localhost:9090/api/v1/users" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

## 🐳 Docker 설정

### 포트 매핑
- **Spring Boot**: 9090 포트
- **PostgreSQL**: 5432 포트 (공유)

### 환경 변수
- `SPRING_DATASOURCE_URL`: 데이터베이스 URL
- `SPRING_DATASOURCE_USERNAME`: DB 사용자명
- `SPRING_DATASOURCE_PASSWORD`: DB 비밀번호
- `JWT_SECRET`: JWT 비밀키
- `JWT_EXPIRATION`: JWT 만료 시간

## 🛠️ 개발 도구

### 코드 포맷팅
```bash
# Spotless 플러그인 사용 (pom.xml에 추가 시)
mvn spotless:apply
```

### 테스트
```bash
# 단위 테스트 실행
mvn test

# 통합 테스트 포함
mvn verify
```

### 빌드
```bash
# JAR 파일 생성
mvn clean package

# Docker 이미지 빌드
docker build -t sample-intranet-java .
```

## 🔒 보안 기능

- **JWT 토큰** 기반 인증
- **BCrypt**로 패스워드 해싱
- **Spring Security**를 통한 보안 설정
- **역할 기반 접근 제어** (RBAC)
- **CORS** 설정
- **입력 값 검증** (Bean Validation)

## 🆚 다른 백엔드와 비교

| 특징 | Go | Python | **Java** |
|------|----|---------| -------- |
| **성능** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐⭐ |
| **개발 속도** | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ |
| **엔터프라이즈** | ⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| **생태계** | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| **타입 안정성** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| **학습 곡선** | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ |

## 📝 주의사항

1. **운영 환경**에서는 `JWT_SECRET`을 강력한 키로 변경하세요
2. **CORS 설정**을 운영 환경에 맞게 제한하세요
3. **로깅 레벨**을 운영에서는 `INFO` 이상으로 설정하세요
4. **데이터베이스 연결 풀** 설정을 최적화하세요

## 🤝 기여하기

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 라이선스

이 프로젝트는 MIT 라이선스 하에 배포됩니다.

---

**💡 Java 백엔드의 장점**: 강력한 타입 시스템, 풍부한 생태계, 엔터프라이즈급 안정성! 