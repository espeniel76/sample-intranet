# 빌드 스테이지
FROM golang:1.21-alpine AS builder

# 필요한 패키지 설치
RUN apk add --no-cache git

# 작업 디렉토리 설정
WORKDIR /app

# Go 모듈 파일 복사 및 의존성 다운로드
COPY go.mod go.sum ./
RUN go mod download

# 소스 코드 복사
COPY . .

# 애플리케이션 빌드
RUN CGO_ENABLED=0 GOOS=linux go build -a -installsuffix cgo -o main .

# 실행 스테이지
FROM alpine:latest

# 필요한 패키지 설치
RUN apk --no-cache add ca-certificates

# 작업 디렉토리 설정
WORKDIR /app

# 빌드된 바이너리 복사
COPY --from=builder /app/main .
COPY --from=builder /app/config ./config

# 포트 노출
EXPOSE 8080

# 애플리케이션 실행
CMD ["./main"] 