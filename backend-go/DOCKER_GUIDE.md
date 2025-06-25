# Docker 사용 가이드

## 🐳 Docker 설치

### Windows
1. [Docker Desktop for Windows](https://www.docker.com/products/docker-desktop/) 다운로드
2. 설치 파일 실행
3. Docker Desktop 앱 실행
4. 설치 확인: `docker --version`

## 🚀 프로젝트 실행

### 전체 시스템 실행 (PostgreSQL + 백엔드)
```bash
# backend 폴더로 이동
cd backend

# 모든 서비스 실행 (최초 실행시)
docker-compose up --build

# 이후 실행시 (빌드 없이)
docker-compose up
```

### 개별 서비스 실행
```bash
# PostgreSQL만 백그라운드에서 실행
docker-compose up postgres -d

# 백엔드 애플리케이션만 실행
docker-compose up app --build
```

## 📋 주요 Docker 명령어

### Docker Compose 명령어
```bash
# 서비스 시작
docker-compose up

# 백그라운드에서 서비스 시작
docker-compose up -d

# 이미지 새로 빌드하면서 시작
docker-compose up --build

# 서비스 중지
docker-compose down

# 서비스 상태 확인
docker-compose ps

# 로그 확인
docker-compose logs
docker-compose logs app      # 백엔드 로그만
docker-compose logs postgres # PostgreSQL 로그만
```

### Docker 기본 명령어
```bash
# 실행 중인 컨테이너 확인
docker ps

# 모든 컨테이너 확인 (중지된 것 포함)
docker ps -a

# 이미지 목록 확인
docker images

# 컨테이너 중지
docker stop <컨테이너명>

# 컨테이너 삭제
docker rm <컨테이너명>

# 이미지 삭제
docker rmi <이미지명>
```

## 🔧 docker-compose.yml 설명

```yaml
version: '3.8'

services:
  # PostgreSQL 데이터베이스
  postgres:
    image: postgres:15              # PostgreSQL 15 이미지 사용
    container_name: sample_intranet_postgres
    environment:                    # 환경 변수 설정
      POSTGRES_DB: sample_intranet
      POSTGRES_USER: postgres
      POSTGRES_PASSWORD: password
    ports:
      - "5432:5432"                # 호스트:컨테이너 포트 매핑
    volumes:
      - postgres_data:/var/lib/postgresql/data  # 데이터 영구 저장
    healthcheck:                   # 헬스체크 설정
      test: ["CMD-SHELL", "pg_isready -U postgres"]
      interval: 10s
      timeout: 5s
      retries: 5

  # 백엔드 애플리케이션
  app:
    build: .                       # 현재 디렉토리의 Dockerfile로 빌드
    container_name: sample_intranet_backend
    ports:
      - "8080:8080"               # API 서버 포트
    depends_on:
      postgres:
        condition: service_healthy # PostgreSQL 준비된 후 시작
    environment:                  # 환경 변수로 설정 오버라이드
      DATABASE_HOST: postgres
      DATABASE_PORT: 5432
      # ... 기타 설정
    volumes:
      - ./config:/app/config      # 설정 파일 마운트

volumes:
  postgres_data:                  # 데이터베이스 데이터 영구 저장용
```

## 🛠️ Dockerfile 설명

```dockerfile
# 멀티 스테이지 빌드

# === 빌드 스테이지 ===
FROM golang:1.21-alpine AS builder
# Go 컴파일러가 포함된 이미지 사용

WORKDIR /app
# 작업 디렉토리 설정

COPY go.mod go.sum ./
RUN go mod download
# 의존성 먼저 다운로드 (캐시 효율성)

COPY . .
# 소스 코드 복사

RUN CGO_ENABLED=0 GOOS=linux go build -a -installsuffix cgo -o main .
# 정적 바이너리로 빌드

# === 실행 스테이지 ===
FROM alpine:latest
# 경량 리눅스 이미지

RUN apk --no-cache add ca-certificates
# HTTPS 통신을 위한 인증서

WORKDIR /app

COPY --from=builder /app/main .
COPY --from=builder /app/config ./config
# 빌드 스테이지에서 필요한 파일만 복사

EXPOSE 8080
# 8080 포트 오픈

CMD ["./main"]
# 애플리케이션 실행
```

## 🔄 개발 워크플로우

### 1. 최초 설정
```bash
cd backend
docker-compose up --build
```

### 2. 개발 중
```bash
# 코드 수정 후 재빌드
docker-compose up --build app

# 또는 전체 재시작
docker-compose down
docker-compose up --build
```

### 3. 로그 확인
```bash
# 실시간 로그 보기
docker-compose logs -f app

# PostgreSQL 로그 보기
docker-compose logs -f postgres
```

### 4. 데이터베이스 접속
```bash
# PostgreSQL 컨테이너에 접속
docker exec -it sample_intranet_postgres psql -U postgres -d sample_intranet
```

## 🎯 API 테스트

Docker로 실행한 후 API 테스트:
```bash
# 헬스체크
curl http://localhost:8080/health

# 회원가입
curl -X POST http://localhost:8080/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123","name":"테스트"}'

# 로그인
curl -X POST http://localhost:8080/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'
```

## 🛑 중지 및 정리

```bash
# 서비스 중지
docker-compose down

# 데이터까지 모두 삭제
docker-compose down -v

# 빌드 캐시 정리
docker system prune
``` 