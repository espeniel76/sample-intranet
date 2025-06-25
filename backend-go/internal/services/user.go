package services

import (
	"errors"
	"sample-intranet-backend/internal/models"
	"sample-intranet-backend/internal/repositories"

	"github.com/sirupsen/logrus"
	"golang.org/x/crypto/bcrypt"
)

// UserService 사용자 서비스
type UserService struct {
	userRepo *repositories.UserRepository
}

// NewUserService 사용자 서비스 생성
func NewUserService(userRepo *repositories.UserRepository) *UserService {
	return &UserService{
		userRepo: userRepo,
	}
}

// CreateUser 새 사용자 생성
func (s *UserService) CreateUser(req *models.UserCreateRequest) (*models.User, error) {
	// 이메일 중복 확인
	existingUser, err := s.userRepo.GetByEmail(req.Email)
	if err == nil && existingUser != nil {
		logrus.WithField("email", req.Email).Error("이미 존재하는 이메일")
		return nil, errors.New("이미 존재하는 이메일입니다")
	}

	// 비밀번호 해시화
	hashedPassword, err := s.hashPassword(req.Password)
	if err != nil {
		logrus.WithError(err).Error("비밀번호 해시화 실패")
		return nil, err
	}

	// 사용자 객체 생성
	user := &models.User{
		Email:    req.Email,
		Password: hashedPassword,
		Name:     req.Name,
		Role:     req.Role,
		IsActive: true,
	}

	// 기본 역할 설정
	if user.Role == "" {
		user.Role = "user"
	}

	// 데이터베이스에 저장
	createdUser, err := s.userRepo.Create(user)
	if err != nil {
		logrus.WithError(err).Error("사용자 생성 데이터베이스 저장 실패")
		return nil, err
	}

	logrus.WithField("user_id", createdUser.ID).Info("새 사용자 생성 성공")
	return createdUser, nil
}

// AuthenticateUser 사용자 인증
func (s *UserService) AuthenticateUser(email, password string) (*models.User, error) {
	// 이메일로 사용자 조회
	user, err := s.userRepo.GetByEmail(email)
	if err != nil {
		logrus.WithError(err).WithField("email", email).Error("사용자 조회 실패")
		return nil, errors.New("이메일 또는 비밀번호가 올바르지 않습니다")
	}

	// 활성 사용자 확인
	if !user.IsActive {
		logrus.WithField("user_id", user.ID).Error("비활성 사용자 로그인 시도")
		return nil, errors.New("비활성 계정입니다")
	}

	// 비밀번호 검증
	if err := s.verifyPassword(user.Password, password); err != nil {
		logrus.WithField("user_id", user.ID).Error("비밀번호 검증 실패")
		return nil, errors.New("이메일 또는 비밀번호가 올바르지 않습니다")
	}

	logrus.WithField("user_id", user.ID).Info("사용자 인증 성공")
	return user, nil
}

// GetAllUsers 모든 사용자 조회
func (s *UserService) GetAllUsers() ([]*models.User, error) {
	users, err := s.userRepo.GetAll()
	if err != nil {
		logrus.WithError(err).Error("사용자 목록 조회 실패")
		return nil, err
	}

	return users, nil
}

// GetUserByID ID로 사용자 조회
func (s *UserService) GetUserByID(id uint) (*models.User, error) {
	user, err := s.userRepo.GetByID(id)
	if err != nil {
		logrus.WithError(err).WithField("user_id", id).Error("사용자 조회 실패")
		return nil, err
	}

	return user, nil
}

// UpdateUser 사용자 정보 수정
func (s *UserService) UpdateUser(id uint, req *models.UserUpdateRequest) (*models.User, error) {
	// 기존 사용자 조회
	user, err := s.userRepo.GetByID(id)
	if err != nil {
		logrus.WithError(err).WithField("user_id", id).Error("수정할 사용자 조회 실패")
		return nil, err
	}

	// 이메일 중복 확인 (변경하는 경우)
	if req.Email != "" && req.Email != user.Email {
		existingUser, err := s.userRepo.GetByEmail(req.Email)
		if err == nil && existingUser != nil {
			logrus.WithField("email", req.Email).Error("이미 존재하는 이메일로 수정 시도")
			return nil, errors.New("이미 존재하는 이메일입니다")
		}
		user.Email = req.Email
	}

	// 비밀번호 변경 (제공된 경우)
	if req.Password != "" {
		hashedPassword, err := s.hashPassword(req.Password)
		if err != nil {
			logrus.WithError(err).Error("비밀번호 해시화 실패")
			return nil, err
		}
		user.Password = hashedPassword
	}

	// 기타 필드 업데이트
	if req.Name != "" {
		user.Name = req.Name
	}
	if req.Role != "" {
		user.Role = req.Role
	}
	if req.IsActive != nil {
		user.IsActive = *req.IsActive
	}

	// 데이터베이스 업데이트
	updatedUser, err := s.userRepo.Update(user)
	if err != nil {
		logrus.WithError(err).WithField("user_id", id).Error("사용자 정보 업데이트 실패")
		return nil, err
	}

	logrus.WithField("user_id", id).Info("사용자 정보 수정 성공")
	return updatedUser, nil
}

// DeleteUser 사용자 삭제
func (s *UserService) DeleteUser(id uint) error {
	// 사용자 존재 확인
	_, err := s.userRepo.GetByID(id)
	if err != nil {
		logrus.WithError(err).WithField("user_id", id).Error("삭제할 사용자 조회 실패")
		return err
	}

	// 사용자 삭제
	if err := s.userRepo.Delete(id); err != nil {
		logrus.WithError(err).WithField("user_id", id).Error("사용자 삭제 실패")
		return err
	}

	logrus.WithField("user_id", id).Info("사용자 삭제 성공")
	return nil
}

// hashPassword 비밀번호 해시화
func (s *UserService) hashPassword(password string) (string, error) {
	hashedBytes, err := bcrypt.GenerateFromPassword([]byte(password), bcrypt.DefaultCost)
	if err != nil {
		return "", err
	}
	return string(hashedBytes), nil
}

// verifyPassword 비밀번호 검증
func (s *UserService) verifyPassword(hashedPassword, password string) error {
	return bcrypt.CompareHashAndPassword([]byte(hashedPassword), []byte(password))
}
