import bcrypt from 'bcryptjs';
import { UserRole } from '@prisma/client';
import { prisma } from '../utils/database';
import { 
  CreateUserRequest, 
  UpdateUserRequest, 
  UserResponse, 
  UserSearchParams,
  toUserResponse 
} from '../types/user.types';

/**
 * 사용자 서비스 클래스
 * 비즈니스 로직 처리
 */
export class UserService {

  /**
   * 사용자 생성 (회원가입)
   */
  async createUser(userData: CreateUserRequest): Promise<UserResponse> {
    // 이메일 중복 확인
    const existingUser = await prisma.user.findUnique({
      where: { email: userData.email }
    });

    if (existingUser) {
      throw new Error(`이미 존재하는 이메일입니다: ${userData.email}`);
    }

    // 비밀번호 해싱
    const hashedPassword = await bcrypt.hash(userData.password, 10);

    // 새 사용자 생성
    const user = await prisma.user.create({
      data: {
        email: userData.email,
        password: hashedPassword,
        name: userData.name,
        role: userData.role || UserRole.USER,
        isActive: userData.isActive ?? true
      }
    });

    return toUserResponse(user);
  }

  /**
   * 사용자 인증 (로그인)
   */
  async authenticateUser(email: string, password: string): Promise<UserResponse | null> {
    // 활성 사용자 찾기
    const user = await prisma.user.findFirst({
      where: { 
        email,
        isActive: true
      }
    });

    if (!user) {
      return null;
    }

    // 비밀번호 검증
    const isPasswordValid = await bcrypt.compare(password, user.password);
    if (!isPasswordValid) {
      return null;
    }

    return toUserResponse(user);
  }

  /**
   * 모든 활성 사용자 조회
   */
  async getAllUsers(searchParams?: UserSearchParams): Promise<UserResponse[]> {
    const { skip = 0, limit = 100, name } = searchParams || {};

    const users = await prisma.user.findMany({
      where: {
        isActive: true,
        ...(name && { 
          name: { 
            contains: name, 
            mode: 'insensitive' 
          } 
        })
      },
      skip,
      take: limit,
      orderBy: { createdAt: 'desc' }
    });

    return users.map(toUserResponse);
  }

  /**
   * ID로 사용자 조회
   */
  async getUserById(id: number): Promise<UserResponse | null> {
    const user = await prisma.user.findUnique({
      where: { id }
    });

    return user ? toUserResponse(user) : null;
  }

  /**
   * 이메일로 사용자 조회
   */
  async getUserByEmail(email: string): Promise<UserResponse | null> {
    const user = await prisma.user.findUnique({
      where: { email }
    });

    return user ? toUserResponse(user) : null;
  }

  /**
   * 사용자 정보 업데이트
   */
  async updateUser(id: number, updateData: UpdateUserRequest): Promise<UserResponse> {
    // 사용자 존재 확인
    const existingUser = await prisma.user.findUnique({
      where: { id }
    });

    if (!existingUser) {
      throw new Error(`사용자를 찾을 수 없습니다: ${id}`);
    }

    // 사용자 정보 업데이트
    const updatedUser = await prisma.user.update({
      where: { id },
      data: updateData
    });

    return toUserResponse(updatedUser);
  }

  /**
   * 사용자 삭제
   */
  async deleteUser(id: number): Promise<void> {
    // 사용자 존재 확인
    const existingUser = await prisma.user.findUnique({
      where: { id }
    });

    if (!existingUser) {
      throw new Error(`사용자를 찾을 수 없습니다: ${id}`);
    }

    // 사용자 삭제
    await prisma.user.delete({
      where: { id }
    });
  }

  /**
   * 이름으로 사용자 검색
   */
  async searchUsersByName(name: string): Promise<UserResponse[]> {
    const users = await prisma.user.findMany({
      where: {
        isActive: true,
        name: {
          contains: name,
          mode: 'insensitive'
        }
      },
      orderBy: { createdAt: 'desc' }
    });

    return users.map(toUserResponse);
  }

  /**
   * 사용자 수 조회
   */
  async getUserCount(): Promise<number> {
    return await prisma.user.count({
      where: { isActive: true }
    });
  }
} 