# 멀티 스테이지 빌드
# 1단계: 빌드 스테이지
FROM php:8.2-cli-alpine AS builder

# 필요한 패키지 설치
RUN apk add --no-cache \
    postgresql-dev \
    zip \
    unzip \
    git \
    curl

# PHP 확장 설치
RUN docker-php-ext-install pdo pdo_pgsql

# Composer 설치
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 작업 디렉토리 설정
WORKDIR /app

# Composer 의존성 파일 복사
COPY composer.json composer.lock ./

# 의존성 설치 (최적화)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 2단계: 실행 스테이지
FROM php:8.2-cli-alpine AS runner

# 필요한 패키지 설치
RUN apk add --no-cache \
    postgresql-dev \
    && docker-php-ext-install pdo pdo_pgsql

# 작업 디렉토리 설정
WORKDIR /app

# 빌드된 의존성 복사
COPY --from=builder /app/vendor ./vendor

# 애플리케이션 코드 복사
COPY . .

# 권한 설정
RUN chmod -R 755 /app \
    && mkdir -p logs \
    && chmod 777 logs

# 환경 변수 설정
ENV APP_DEBUG=false
ENV DB_HOST=sample_intranet_postgres_shared
ENV DB_PORT=5432
ENV DB_NAME=sample_intranet
ENV DB_USERNAME=postgres
ENV DB_PASSWORD=password

# 포트 노출
EXPOSE 8090

# 헬스체크 추가
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
  CMD php -r "echo file_get_contents('http://localhost:8090/health') ? 'OK' : exit(1);"

# 애플리케이션 실행
CMD ["php", "-S", "0.0.0.0:8090", "-t", "public"] 