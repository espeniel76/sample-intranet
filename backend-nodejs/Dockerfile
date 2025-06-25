# 멀티 스테이지 빌드
# 1단계: 빌드 스테이지
FROM node:18-alpine AS builder

# 작업 디렉토리 설정
WORKDIR /app

# package.json과 package-lock.json 복사
COPY package*.json ./
COPY prisma ./prisma/

# 의존성 설치
RUN npm ci --only=production && npm cache clean --force

# Prisma 클라이언트 생성
RUN npx prisma generate

# TypeScript 컴파일러 설치 및 빌드
RUN npm install typescript -g
COPY . .
RUN npm run build

# 2단계: 실행 스테이지  
FROM node:18-alpine AS runner

# 작업 디렉토리 설정
WORKDIR /app

# 프로덕션 의존성만 설치
COPY package*.json ./
RUN npm ci --only=production && npm cache clean --force

# Prisma 관련 파일 복사
COPY prisma ./prisma/
RUN npx prisma generate

# 빌드된 애플리케이션 복사
COPY --from=builder /app/dist ./dist

# 환경 변수 설정
ENV NODE_ENV=production
ENV PORT=3000

# 포트 노출
EXPOSE 3000

# 헬스체크 추가
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
  CMD node -e "http.get('http://localhost:3000/api/v1/health', (res) => { process.exit(res.statusCode === 200 ? 0 : 1) }).on('error', () => process.exit(1))"

# 애플리케이션 실행
CMD ["node", "dist/server.js"] 