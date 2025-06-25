package models

import (
	"time"

	"gorm.io/gorm"
)

// User 사용자 모델
type User struct {
	ID        uint           `json:"id" gorm:"primaryKey"`
	Email     string         `json:"email" gorm:"uniqueIndex;not null" validate:"required,email"`
	Password  string         `json:"-" gorm:"not null" validate:"required,min=6"`
	Name      string         `json:"name" gorm:"not null" validate:"required,min=2,max=50"`
	Role      string         `json:"role" gorm:"default:user" validate:"oneof=user admin"`
	IsActive  bool           `json:"is_active" gorm:"default:true"`
	CreatedAt time.Time      `json:"created_at"`
	UpdatedAt time.Time      `json:"updated_at"`
	DeletedAt gorm.DeletedAt `json:"-" gorm:"index"`
}

// UserCreateRequest 사용자 생성 요청 구조체
type UserCreateRequest struct {
	Email    string `json:"email" validate:"required,email"`
	Password string `json:"password" validate:"required,min=6"`
	Name     string `json:"name" validate:"required,min=2,max=50"`
	Role     string `json:"role" validate:"omitempty,oneof=user admin"`
}

// UserUpdateRequest 사용자 수정 요청 구조체
type UserUpdateRequest struct {
	Email    string `json:"email" validate:"omitempty,email"`
	Password string `json:"password" validate:"omitempty,min=6"`
	Name     string `json:"name" validate:"omitempty,min=2,max=50"`
	Role     string `json:"role" validate:"omitempty,oneof=user admin"`
	IsActive *bool  `json:"is_active"`
}

// LoginRequest 로그인 요청 구조체
type LoginRequest struct {
	Email    string `json:"email" validate:"required,email"`
	Password string `json:"password" validate:"required"`
}

// UserResponse 사용자 응답 구조체 (비밀번호 제외)
type UserResponse struct {
	ID        uint      `json:"id"`
	Email     string    `json:"email"`
	Name      string    `json:"name"`
	Role      string    `json:"role"`
	IsActive  bool      `json:"is_active"`
	CreatedAt time.Time `json:"created_at"`
	UpdatedAt time.Time `json:"updated_at"`
}

// ToResponse User 모델을 UserResponse로 변환
func (u *User) ToResponse() *UserResponse {
	return &UserResponse{
		ID:        u.ID,
		Email:     u.Email,
		Name:      u.Name,
		Role:      u.Role,
		IsActive:  u.IsActive,
		CreatedAt: u.CreatedAt,
		UpdatedAt: u.UpdatedAt,
	}
}
