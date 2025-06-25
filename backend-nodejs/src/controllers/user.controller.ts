import { Request, Response } from 'express';
import { validationResult } from 'express-validator';
import { UserService } from '../services/user.service';
import { generateToken, getTokenExpirationTime } from '../utils/jwt.utils';
import { 
  CreateUserRequest, 
  LoginRequest, 
  UpdateUserRequest,
  JwtResponse, 
  HealthResponse,
  ApiResponse
} from '../types/user.types';

/**
 * 사용자 컨트롤러 클래스
 * REST API 엔드포인트 처리
 */
export class UserController {
  private userService: UserService;

  constructor() {
    this.userService = new UserService();
  }

  /**
   * 헬스체크 엔드포인트
   */
  healthCheck = async (req: Request, res: Response): Promise<void> => {
    const healthData: HealthResponse = {
      status: 'healthy',
      timestamp: Math.floor(Date.now() / 1000),
      app_name: 'Sample Intranet Backend Node.js'
    };
    res.json(healthData);
  };

  /**
   * 회원가입
   */
  register = async (req: Request, res: Response): Promise<void> => {
    try {
      // 입력 검증 확인
      const errors = validationResult(req);
      if (!errors.isEmpty()) {
        res.status(400).json({
          error: '유효성 검사 실패',
          detail: errors.array().map(err => err.msg).join(', ')
        });
        return;
      }

      const userData: CreateUserRequest = req.body;
      const user = await this.userService.createUser(userData);

      res.status(201).json(user);
    } catch (error) {
      res.status(400).json({
        error: '회원가입 실패',
        detail: error instanceof Error ? error.message : '알 수 없는 오류'
      });
    }
  };

  /**
   * 로그인
   */
  login = async (req: Request, res: Response): Promise<void> => {
    try {
      // 입력 검증 확인
      const errors = validationResult(req);
      if (!errors.isEmpty()) {
        res.status(400).json({
          error: '유효성 검사 실패',
          detail: errors.array().map(err => err.msg).join(', ')
        });
        return;
      }

      const { email, password }: LoginRequest = req.body;
      const user = await this.userService.authenticateUser(email, password);

      if (!user) {
        res.status(401).json({
          error: '로그인 실패',
          detail: '이메일 또는 비밀번호가 올바르지 않습니다'
        });
        return;
      }

      // JWT 토큰 생성
      const token = generateToken({
        userId: user.id,
        email: user.email,
        role: user.role
      });

      const response: JwtResponse = {
        accessToken: token,
        tokenType: 'bearer',
        expiresIn: `${getTokenExpirationTime()}s`,
        user
      };

      res.json(response);
    } catch (error) {
      res.status(500).json({
        error: '로그인 처리 중 오류 발생',
        detail: error instanceof Error ? error.message : '알 수 없는 오류'
      });
    }
  };

  /**
   * 사용자 목록 조회
   */
  getUsers = async (req: Request, res: Response): Promise<void> => {
    try {
      const skip = parseInt(req.query.skip as string) || 0;
      const limit = parseInt(req.query.limit as string) || 100;
      const name = req.query.name as string;

      const users = await this.userService.getAllUsers({ skip, limit, name });
      res.json(users);
    } catch (error) {
      res.status(500).json({
        error: '사용자 목록 조회 실패',
        detail: error instanceof Error ? error.message : '알 수 없는 오류'
      });
    }
  };

  /**
   * 특정 사용자 조회
   */
  getUser = async (req: Request, res: Response): Promise<void> => {
    try {
      const id = parseInt(req.params.id);
      if (isNaN(id)) {
        res.status(400).json({
          error: '잘못된 사용자 ID',
          detail: '유효한 숫자 ID를 입력해주세요'
        });
        return;
      }

      const user = await this.userService.getUserById(id);
      if (!user) {
        res.status(404).json({
          error: '사용자를 찾을 수 없습니다',
          detail: `ID ${id}에 해당하는 사용자가 없습니다`
        });
        return;
      }

      res.json(user);
    } catch (error) {
      res.status(500).json({
        error: '사용자 조회 실패',
        detail: error instanceof Error ? error.message : '알 수 없는 오류'
      });
    }
  };

  /**
   * 사용자 정보 수정
   */
  updateUser = async (req: Request, res: Response): Promise<void> => {
    try {
      const id = parseInt(req.params.id);
      if (isNaN(id)) {
        res.status(400).json({
          error: '잘못된 사용자 ID',
          detail: '유효한 숫자 ID를 입력해주세요'
        });
        return;
      }

      const updateData: UpdateUserRequest = req.body;
      const updatedUser = await this.userService.updateUser(id, updateData);

      res.json(updatedUser);
    } catch (error) {
      res.status(400).json({
        error: '사용자 정보 수정 실패',
        detail: error instanceof Error ? error.message : '알 수 없는 오류'
      });
    }
  };

  /**
   * 사용자 삭제 (관리자 전용)
   */
  deleteUser = async (req: Request, res: Response): Promise<void> => {
    try {
      const id = parseInt(req.params.id);
      if (isNaN(id)) {
        res.status(400).json({
          error: '잘못된 사용자 ID',
          detail: '유효한 숫자 ID를 입력해주세요'
        });
        return;
      }

      await this.userService.deleteUser(id);

      const response: ApiResponse = {
        success: true,
        message: '사용자가 삭제되었습니다'
      };

      res.json(response);
    } catch (error) {
      res.status(400).json({
        error: '사용자 삭제 실패',
        detail: error instanceof Error ? error.message : '알 수 없는 오류'
      });
    }
  };

  /**
   * 사용자 검색
   */
  searchUsers = async (req: Request, res: Response): Promise<void> => {
    try {
      const name = req.query.name as string;
      if (!name) {
        res.status(400).json({
          error: '검색어 필요',
          detail: 'name 파라미터를 입력해주세요'
        });
        return;
      }

      const users = await this.userService.searchUsersByName(name);
      res.json(users);
    } catch (error) {
      res.status(500).json({
        error: '사용자 검색 실패',
        detail: error instanceof Error ? error.message : '알 수 없는 오류'
      });
    }
  };
} 