import jwt from 'jsonwebtoken';
import { JwtPayload } from '../types/user.types';

const JWT_SECRET = process.env.JWT_SECRET || 'your-secret-key';
const JWT_EXPIRES_IN = process.env.JWT_EXPIRES_IN || '24h';

/**
 * JWT 토큰 생성
 */
export const generateToken = (payload: JwtPayload): string => {
  return jwt.sign(payload, JWT_SECRET, { expiresIn: JWT_EXPIRES_IN });
};

/**
 * JWT 토큰 검증 및 디코딩
 */
export const verifyToken = (token: string): JwtPayload => {
  try {
    const decoded = jwt.verify(token, JWT_SECRET) as JwtPayload;
    return decoded;
  } catch (error) {
    throw new Error('Invalid or expired token');
  }
};

/**
 * JWT 토큰에서 Bearer 접두사 제거
 */
export const extractToken = (authHeader: string | undefined): string => {
  if (!authHeader) {
    throw new Error('Authorization header missing');
  }

  if (!authHeader.startsWith('Bearer ')) {
    throw new Error('Invalid authorization format');
  }

  return authHeader.substring(7); // 'Bearer ' 제거
};

/**
 * JWT 만료 시간을 초 단위로 반환
 */
export const getTokenExpirationTime = (): number => {
  // 24h -> 24 * 60 * 60 = 86400 seconds
  if (JWT_EXPIRES_IN.endsWith('h')) {
    return parseInt(JWT_EXPIRES_IN.slice(0, -1)) * 3600;
  }
  if (JWT_EXPIRES_IN.endsWith('d')) {
    return parseInt(JWT_EXPIRES_IN.slice(0, -1)) * 86400;
  }
  // 기본값 24시간
  return 86400;
}; 