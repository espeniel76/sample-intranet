import { Request, Response, NextFunction } from 'express';
import { UserRole } from '@prisma/client';
import { verifyToken, extractToken } from '../utils/jwt.utils';
import { JwtPayload } from '../types/user.types';

// Request 인터페이스 확장하여 user 정보 추가
declare global {
  namespace Express {
    interface Request {
      user?: JwtPayload;
    }
  }
}

/**
 * JWT 토큰 인증 미들웨어
 * Authorization 헤더에서 토큰을 추출하고 검증
 */
export const authenticateToken = (req: Request, res: Response, next: NextFunction): void => {
  try {
    const authHeader = req.headers.authorization;
    const token = extractToken(authHeader);
    const decoded = verifyToken(token);
    
    // 요청 객체에 사용자 정보 추가
    req.user = decoded;
    next();
  } catch (error) {
    res.status(401).json({
      error: '인증 실패',
      detail: error instanceof Error ? error.message : '유효하지 않은 토큰'
    });
  }
};

/**
 * 관리자 권한 확인 미들웨어
 * 인증된 사용자가 관리자인지 확인
 */
export const requireAdmin = (req: Request, res: Response, next: NextFunction): void => {
  if (!req.user) {
    res.status(401).json({
      error: '인증 필요',
      detail: '로그인이 필요합니다'
    });
    return;
  }

  if (req.user.role !== UserRole.ADMIN) {
    res.status(403).json({
      error: '권한 부족',
      detail: '관리자 권한이 필요합니다'
    });
    return;
  }

  next();
};

/**
 * 사용자 본인 또는 관리자 권한 확인 미들웨어
 * 본인의 정보이거나 관리자인 경우에만 허용
 */
export const requireOwnerOrAdmin = (req: Request, res: Response, next: NextFunction): void => {
  if (!req.user) {
    res.status(401).json({
      error: '인증 필요',
      detail: '로그인이 필요합니다'
    });
    return;
  }

  const targetUserId = parseInt(req.params.id);
  const currentUserId = req.user.userId;
  const isAdmin = req.user.role === UserRole.ADMIN;

  if (currentUserId !== targetUserId && !isAdmin) {
    res.status(403).json({
      error: '권한 부족',
      detail: '본인의 정보이거나 관리자 권한이 필요합니다'
    });
    return;
  }

  next();
};

/**
 * 선택적 인증 미들웨어
 * 토큰이 있으면 인증하되, 없어도 통과
 */
export const optionalAuth = (req: Request, res: Response, next: NextFunction): void => {
  try {
    const authHeader = req.headers.authorization;
    if (authHeader) {
      const token = extractToken(authHeader);
      const decoded = verifyToken(token);
      req.user = decoded;
    }
  } catch (error) {
    // 선택적 인증이므로 에러가 있어도 무시하고 진행
  }
  next();
}; 