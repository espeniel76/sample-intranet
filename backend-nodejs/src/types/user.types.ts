import { User, UserRole } from '@prisma/client';

// 사용자 생성 요청 타입
export interface CreateUserRequest {
  email: string;
  password: string;
  name: string;
  role?: UserRole;
  isActive?: boolean;
}

// 사용자 로그인 요청 타입
export interface LoginRequest {
  email: string;
  password: string;
}

// 사용자 업데이트 요청 타입
export interface UpdateUserRequest {
  name?: string;
  role?: UserRole;
  isActive?: boolean;
}

// 사용자 응답 타입 (비밀번호 제외)
export interface UserResponse {
  id: number;
  email: string;
  name: string;
  role: UserRole;
  isActive: boolean;
  createdAt: Date;
  updatedAt: Date;
}

// JWT 토큰 응답 타입
export interface JwtResponse {
  accessToken: string;
  tokenType: string;
  expiresIn: string;
  user: UserResponse;
}

// JWT 페이로드 타입
export interface JwtPayload {
  userId: number;
  email: string;
  role: UserRole;
}

// API 응답 타입
export interface ApiResponse<T = any> {
  success: boolean;
  data?: T;
  error?: string;
  detail?: string;
  message?: string;
}

// 헬스체크 응답 타입
export interface HealthResponse {
  status: string;
  timestamp: number;
  app_name: string;
}

// 사용자 검색 파라미터 타입
export interface UserSearchParams {
  name?: string;
  skip?: number;
  limit?: number;
}

// User를 UserResponse로 변환하는 유틸리티 함수
export const toUserResponse = (user: User): UserResponse => ({
  id: user.id,
  email: user.email,
  name: user.name,
  role: user.role,
  isActive: user.isActive,
  createdAt: user.createdAt,
  updatedAt: user.updatedAt
}); 