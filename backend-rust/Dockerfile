# 멀티 스테이지 빌드로 최적화된 Rust 이미지 생성
FROM rust:1.75-slim as builder

# 작업 디렉토리 설정
WORKDIR /app

# 시스템 의존성 설치 (빌드에 필요한 패키지)
RUN apt-get update && apt-get install -y \
    pkg-config \
    libssl-dev \
    ca-certificates \
    && rm -rf /var/lib/apt/lists/*

# Cargo.toml과 Cargo.lock 먼저 복사 (의존성 캐싱을 위해)
COPY Cargo.toml Cargo.lock ./

# 빈 src/main.rs 생성하여 의존성만 먼저 빌드 (캐싱 최적화)
RUN mkdir -p src && echo "fn main() {}" > src/main.rs
RUN cargo build --release
RUN rm src/main.rs

# 실제 소스 코드 복사
COPY src ./src

# Release 모드로 애플리케이션 빌드
RUN cargo build --release

# 실행 단계: 최소한의 런타임 이미지
FROM debian:bookworm-slim

# 런타임 의존성 설치
RUN apt-get update && apt-get install -y \
    ca-certificates \
    libssl3 \
    && rm -rf /var/lib/apt/lists/*

# 작업 디렉토리 설정
WORKDIR /app

# 빌드된 바이너리를 복사
COPY --from=builder /app/target/release/sample-intranet-rust ./

# 애플리케이션 실행을 위한 사용자 생성
RUN useradd -m -u 1001 appuser && chown -R appuser:appuser /app
USER appuser

# 포트 노출
EXPOSE 8070

# 애플리케이션 실행
CMD ["./sample-intranet-rust"] 