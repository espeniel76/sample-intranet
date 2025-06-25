package repositories

import (
	"sample-intranet-backend/internal/models"

	"github.com/sirupsen/logrus"
	"gorm.io/gorm"
)

// UserRepository 사용자 레포지토리
type UserRepository struct {
	db *gorm.DB
}

// NewUserRepository 사용자 레포지토리 생성
func NewUserRepository(db *gorm.DB) *UserRepository {
	return &UserRepository{
		db: db,
	}
}

// Create 새 사용자 생성
func (r *UserRepository) Create(user *models.User) (*models.User, error) {
	if err := r.db.Create(user).Error; err != nil {
		logrus.WithError(err).Error("사용자 생성 실패")
		return nil, err
	}
	return user, nil
}

// GetByID ID로 사용자 조회
func (r *UserRepository) GetByID(id uint) (*models.User, error) {
	var user models.User
	if err := r.db.First(&user, id).Error; err != nil {
		if err == gorm.ErrRecordNotFound {
			logrus.WithField("user_id", id).Error("사용자를 찾을 수 없음")
			return nil, err
		}
		logrus.WithError(err).WithField("user_id", id).Error("사용자 조회 실패")
		return nil, err
	}
	return &user, nil
}

// GetByEmail 이메일로 사용자 조회
func (r *UserRepository) GetByEmail(email string) (*models.User, error) {
	var user models.User
	if err := r.db.Where("email = ?", email).First(&user).Error; err != nil {
		if err == gorm.ErrRecordNotFound {
			logrus.WithField("email", email).Error("사용자를 찾을 수 없음")
			return nil, err
		}
		logrus.WithError(err).WithField("email", email).Error("사용자 조회 실패")
		return nil, err
	}
	return &user, nil
}

// GetAll 모든 사용자 조회
func (r *UserRepository) GetAll() ([]*models.User, error) {
	var users []*models.User
	if err := r.db.Find(&users).Error; err != nil {
		logrus.WithError(err).Error("사용자 목록 조회 실패")
		return nil, err
	}
	return users, nil
}

// Update 사용자 정보 수정
func (r *UserRepository) Update(user *models.User) (*models.User, error) {
	if err := r.db.Save(user).Error; err != nil {
		logrus.WithError(err).WithField("user_id", user.ID).Error("사용자 정보 수정 실패")
		return nil, err
	}
	return user, nil
}

// Delete 사용자 삭제
func (r *UserRepository) Delete(id uint) error {
	if err := r.db.Delete(&models.User{}, id).Error; err != nil {
		logrus.WithError(err).WithField("user_id", id).Error("사용자 삭제 실패")
		return err
	}
	return nil
}

// GetActiveUsers 활성 사용자만 조회
func (r *UserRepository) GetActiveUsers() ([]*models.User, error) {
	var users []*models.User
	if err := r.db.Where("is_active = ?", true).Find(&users).Error; err != nil {
		logrus.WithError(err).Error("활성 사용자 목록 조회 실패")
		return nil, err
	}
	return users, nil
}

// CountUsers 사용자 수 조회
func (r *UserRepository) CountUsers() (int64, error) {
	var count int64
	if err := r.db.Model(&models.User{}).Count(&count).Error; err != nil {
		logrus.WithError(err).Error("사용자 수 조회 실패")
		return 0, err
	}
	return count, nil
}
