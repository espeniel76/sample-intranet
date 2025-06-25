# 🟢 Sample Intranet Backend (Node.js)

TypeScript와 Express.js를 사용한 회원관리 백엔드 API 서버입니다.

## 🚀 기술 스택

- **Runtime**: Node.js 18+
- **Language**: TypeScript
- **Framework**: Express.js
- **ORM**: Prisma
- **Database**: PostgreSQL
- **Authentication**: JWT + bcrypt
- **Validation**: express-validator
- **Security**: Helmet, CORS, Rate Limiting
- **Package Manager**: npm

## 📁 프로젝트 구조

```
backend-nodejs/
├── src/
│   ├── types/
│   │   └── user.types.ts         # 타입 정의
│   ├── utils/
│   │   ├── database.ts           # 데이터베이스 연결
│   │   └── jwt.utils.ts          # JWT 유틸리티
│   ├── middleware/
│   │   └── auth.middleware.ts    # 인증 미들웨어
│   ├── services/
│   │   └── user.service.ts       # 비즈니스 로직
│   ├── controllers/
│   │   └── user.controller.ts    # API 컨트롤러
│   ├── routes/
│   │   └── user.routes.ts        # 라우터 설정
│   └── server.ts                 # 메인 서버 파일
├── prisma/
│   └── schema.prisma             # 데이터베이스 스키마
├── package.json                  # 패키지 설정
├── tsconfig.json                 # TypeScript 설정
├── Dockerfile                    # 도커 이미지 설정
├── docker-compose.yml            # 도커 컴포즈 설정
├── env.example                   # 환경 변수 예시
└── README.md                     # 이 파일
```

## 🔧 설정

### 1. 개발 환경 요구사항
- **Node.js 18** 이상
- **npm 8** 이상
- **PostgreSQL** (Docker 또는 로컬 설치)

### 2. 의존성 설치
```bash
# npm 의존성 설치
npm install

# Prisma 클라이언트 생성
npm run generate

# 데이터베이스 마이그레이션 (선택사항)
npm run migrate
```

### 3. 환경 변수 설정
`.env` 파일을 생성하거나 환경 변수 설정:

```env
# 데이터베이스 설정 (공유 데이터베이스)
DATABASE_URL="postgresql://postgres:password@localhost:5432/sample_intranet"

# JWT 설정
JWT_SECRET="your-super-secret-jwt-key-change-this-in-production-nodejs"
JWT_EXPIRES_IN="24h"

# 서버 설정
PORT=3000
NODE_ENV="development"

# CORS 설정
CORS_ORIGINS="*"

# 레이트 리밋 설정
RATE_LIMIT_WINDOW_MS=900000   # 15분
RATE_LIMIT_MAX_REQUESTS=100   # 최대 요청 수
```

## 🏃‍♂️ 실행 방법

### 로컬 개발 환경
```bash
# 개발 모드 (nodemon 사용)
npm run dev

# 빌드 후 실행
npm run build
npm start

# 타입 체크만
npm run lint
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
curl -X POST "http://localhost:3000/api/v1/auth/register" \
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
curl -X POST "http://localhost:3000/api/v1/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

### 사용자 목록 조회 (인증 토큰 필요)
```bash
curl -X GET "http://localhost:3000/api/v1/users" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

## 🐳 Docker 설정

### 포트 매핑
- **Express.js**: 3000 포트
- **PostgreSQL**: 5432 포트 (공유)

### 환경 변수
- `DATABASE_URL`: 데이터베이스 연결 URL
- `JWT_SECRET`: JWT 비밀키
- `JWT_EXPIRES_IN`: JWT 만료 시간
- `NODE_ENV`: 환경 설정
- `CORS_ORIGINS`: CORS 허용 오리진

## 🛠️ 개발 도구

### 스크립트
```bash
# 개발 서버 실행
npm run dev

# TypeScript 빌드
npm run build

# 프로덕션 서버 실행
npm start

# 코드 검사
npm run lint

# Prisma 관련
npm run generate    # 클라이언트 생성
npm run migrate     # 마이그레이션 적용
npm run studio      # Prisma Studio 실행

# 테스트
npm test
```

### Prisma 명령어
```bash
# 데이터베이스 스키마 초기화
npx prisma migrate dev --name init

# Prisma Studio 실행 (데이터베이스 GUI)
npx prisma studio

# 스키마에서 클라이언트 재생성
npx prisma generate
```

## 🔒 보안 기능

- **JWT 토큰** 기반 인증
- **bcrypt**로 패스워드 해싱
- **Helmet**으로 보안 헤더 설정
- **Rate Limiting**으로 요청 제한
- **CORS** 설정
- **입력 값 검증** (express-validator)
- **SQL 인젝션 방지** (Prisma ORM)

## 🆚 다른 백엔드와 비교

| 특징 | Go | Python | Java | **Node.js** |
|------|----|---------| -------- | ----------- |
| **성능** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐⭐ |
| **개발 속도** | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| **생태계** | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| **타입 안정성** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ |
| **학습 곡선** | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| **비동기 처리** | ⭐⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ |

## 📊 프로젝트 특징

### ✅ 장점
- **빠른 개발**: TypeScript + Express.js로 신속한 프로토타이핑
- **우수한 생태계**: npm 패키지 생태계의 풍부함
- **비동기 처리**: 단일 스레드 이벤트 루프의 효율성
- **타입 안전성**: TypeScript로 컴파일 타임 오류 검출
- **실시간 기능**: WebSocket 등 실시간 기능 구현 용이

### ⚠️ 고려사항
- **CPU 집약적 작업**: 단일 스레드로 인한 성능 제약
- **타입 시스템**: 런타임에서의 타입 검증 한계
- **메모리 사용**: V8 엔진의 메모리 사용량

## 📝 주의사항

1. **운영 환경**에서는 `JWT_SECRET`을 강력한 키로 변경하세요
2. **CORS 설정**을 운영 환경에 맞게 제한하세요
3. **레이트 리밋**을 적절히 조정하세요
4. **데이터베이스 연결 풀** 설정을 최적화하세요
5. **로깅 레벨**을 운영에서는 적절히 설정하세요

## 🤝 기여하기

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 라이선스

이 프로젝트는 MIT 라이선스 하에 배포됩니다.

---

**💡 Node.js 백엔드의 장점**: 빠른 개발 속도, 풍부한 생태계, 우수한 비동기 처리 능력! 