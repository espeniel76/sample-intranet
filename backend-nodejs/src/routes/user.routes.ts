import { Router } from 'express';
import { body } from 'express-validator';
import { UserController } from '../controllers/user.controller';
import { 
  authenticateToken, 
  requireAdmin, 
  requireOwnerOrAdmin 
} from '../middleware/auth.middleware';

const router = Router();
const userController = new UserController();

// 헬스체크 (공개)
router.get('/health', userController.healthCheck);

// 인증 관련 (공개)
router.post('/auth/register', [
  body('email')
    .isEmail()
    .withMessage('올바른 이메일 형식이 아닙니다')
    .normalizeEmail(),
  body('password')
    .isLength({ min: 6 })
    .withMessage('비밀번호는 최소 6자 이상이어야 합니다'),
  body('name')
    .trim()
    .isLength({ min: 1 })
    .withMessage('이름은 필수입니다')
    .escape(),
  body('role')
    .optional()
    .isIn(['USER', 'ADMIN'])
    .withMessage('역할은 USER 또는 ADMIN이어야 합니다'),
  body('isActive')
    .optional()
    .isBoolean()
    .withMessage('활성 상태는 boolean 값이어야 합니다')
], userController.register);

router.post('/auth/login', [
  body('email')
    .isEmail()
    .withMessage('올바른 이메일 형식이 아닙니다')
    .normalizeEmail(),
  body('password')
    .notEmpty()
    .withMessage('비밀번호는 필수입니다')
], userController.login);

// 사용자 관리 (인증 필요)
router.get('/users', authenticateToken, userController.getUsers);
router.get('/users/search', authenticateToken, userController.searchUsers);
router.get('/users/:id', authenticateToken, userController.getUser);

// 사용자 정보 수정 (본인 또는 관리자)
router.put('/users/:id', [
  authenticateToken,
  requireOwnerOrAdmin,
  body('name')
    .optional()
    .trim()
    .isLength({ min: 1 })
    .withMessage('이름은 최소 1자 이상이어야 합니다')
    .escape(),
  body('role')
    .optional()
    .isIn(['USER', 'ADMIN'])
    .withMessage('역할은 USER 또는 ADMIN이어야 합니다'),
  body('isActive')
    .optional()
    .isBoolean()
    .withMessage('활성 상태는 boolean 값이어야 합니다')
], userController.updateUser);

// 관리자 전용
router.delete('/admin/users/:id', [
  authenticateToken,
  requireAdmin
], userController.deleteUser);

export default router; 