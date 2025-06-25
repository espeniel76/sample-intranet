import express from 'express';
import cors from 'cors';
import helmet from 'helmet';
import rateLimit from 'express-rate-limit';
import dotenv from 'dotenv';
import userRoutes from './routes/user.routes';
import { checkDatabaseConnection, disconnectDatabase } from './utils/database';

// 환경 변수 로드
dotenv.config();

const app = express();
const PORT = process.env.PORT || 3000;

/**
 * 레이트 리밋 설정
 */
const limiter = rateLimit({
  windowMs: parseInt(process.env.RATE_LIMIT_WINDOW_MS || '900000'), // 15분
  max: parseInt(process.env.RATE_LIMIT_MAX_REQUESTS || '100'), // 최대 요청 수
  message: {
    error: '너무 많은 요청',
    detail: '잠시 후 다시 시도해주세요'
  },
  standardHeaders: true,
  legacyHeaders: false
});

/**
 * CORS 설정
 */
const corsOptions = {
  origin: process.env.CORS_ORIGINS?.split(',') || ['*'],
  credentials: true,
  optionsSuccessStatus: 200
};

// 미들웨어 설정
app.use(helmet()); // 보안 헤더
app.use(cors(corsOptions)); // CORS
app.use(limiter); // 레이트 리밋
app.use(express.json({ limit: '10mb' })); // JSON 파싱
app.use(express.urlencoded({ extended: true, limit: '10mb' })); // URL 인코딩

// 요청 로깅 미들웨어
app.use((req, res, next) => {
  const timestamp = new Date().toISOString();
  console.log(`[${timestamp}] ${req.method} ${req.path} - ${req.ip}`);
  next();
});

// 라우트 설정
app.use('/api/v1', userRoutes);

// 루트 엔드포인트
app.get('/', (req, res) => {
  res.json({
    message: 'Sample Intranet Backend (Node.js) 🚀',
    version: '1.0.0',
    docs: '/api/v1/health',
    endpoints: {
      health: 'GET /api/v1/health',
      register: 'POST /api/v1/auth/register',
      login: 'POST /api/v1/auth/login',
      users: 'GET /api/v1/users',
      userById: 'GET /api/v1/users/:id',
      updateUser: 'PUT /api/v1/users/:id',
      deleteUser: 'DELETE /api/v1/admin/users/:id',
      searchUsers: 'GET /api/v1/users/search?name=검색어'
    }
  });
});

// 404 에러 핸들러
app.use('*', (req, res) => {
  res.status(404).json({
    error: '엔드포인트를 찾을 수 없습니다',
    detail: `${req.method} ${req.originalUrl} 경로가 존재하지 않습니다`,
    availableEndpoints: [
      'GET /api/v1/health',
      'POST /api/v1/auth/register',
      'POST /api/v1/auth/login',
      'GET /api/v1/users',
      'GET /api/v1/users/:id',
      'PUT /api/v1/users/:id',
      'DELETE /api/v1/admin/users/:id',
      'GET /api/v1/users/search'
    ]
  });
});

// 글로벌 에러 핸들러
app.use((error: Error, req: express.Request, res: express.Response, next: express.NextFunction) => {
  console.error('서버 에러:', error);
  
  res.status(500).json({
    error: '내부 서버 오류',
    detail: process.env.NODE_ENV === 'production' 
      ? '서버에서 오류가 발생했습니다' 
      : error.message
  });
});

/**
 * 서버 시작 함수
 */
async function startServer() {
  try {
    // 데이터베이스 연결 확인
    const isDbConnected = await checkDatabaseConnection();
    if (!isDbConnected) {
      console.error('❌ 데이터베이스 연결 실패');
      process.exit(1);
    }

    // 서버 시작
    const server = app.listen(PORT, () => {
      console.log('🚀 Sample Intranet Backend (Node.js) 시작 완료!');
      console.log(`📡 서버 주소: http://localhost:${PORT}`);
      console.log(`🏥 헬스체크: http://localhost:${PORT}/api/v1/health`);
      console.log(`📋 API 문서: http://localhost:${PORT}/`);
      console.log(`🗄️  데이터베이스 연결: ✅`);
      console.log(`🌍 환경: ${process.env.NODE_ENV || 'development'}`);
    });

    // 서버 종료 시 리소스 정리
    const gracefulShutdown = async () => {
      console.log('\n⏹️  서버 종료 신호 수신...');
      
      server.close(async () => {
        console.log('🔌 HTTP 서버 종료');
        
        try {
          await disconnectDatabase();
          console.log('🗄️  데이터베이스 연결 종료');
        } catch (error) {
          console.error('데이터베이스 연결 종료 실패:', error);
        }
        
        console.log('👋 서버 종료 완료');
        process.exit(0);
      });

      // 30초 후 강제 종료
      setTimeout(() => {
        console.error('❌ 강제 종료');
        process.exit(1);
      }, 30000);
    };

    // 종료 신호 처리
    process.on('SIGTERM', gracefulShutdown);
    process.on('SIGINT', gracefulShutdown);

  } catch (error) {
    console.error('❌ 서버 시작 실패:', error);
    process.exit(1);
  }
}

// 서버 시작
if (require.main === module) {
  startServer();
}

export default app; 