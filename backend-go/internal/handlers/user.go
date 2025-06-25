package handlers

import (
	"net/http"
	"sample-intranet-backend/internal/auth"
	"sample-intranet-backend/internal/models"
	"sample-intranet-backend/internal/services"
	"strconv"

	"github.com/gin-gonic/gin"
	"github.com/go-playground/validator/v10"
	"github.com/sirupsen/logrus"
)

// UserHandler 사용자 관련 핸들러
type UserHandler struct {
	userService *services.UserService
	jwtManager  *auth.JWTManager
	validator   *validator.Validate
}

// NewUserHandler 사용자 핸들러 생성
func NewUserHandler(userService *services.UserService, jwtManager *auth.JWTManager) *UserHandler {
	return &UserHandler{
		userService: userService,
		jwtManager:  jwtManager,
		validator:   validator.New(),
	}
}

// CreateUser 사용자 생성 핸들러
func (h *UserHandler) CreateUser(c *gin.Context) {
	var req models.UserCreateRequest

	// JSON 바인딩
	if err := c.ShouldBindJSON(&req); err != nil {
		logrus.WithError(err).Error("사용자 생성 요청 바인딩 실패")
		c.JSON(http.StatusBadRequest, gin.H{"error": "잘못된 요청 형식입니다"})
		return
	}

	// 유효성 검사
	if err := h.validator.Struct(req); err != nil {
		logrus.WithError(err).Error("사용자 생성 요청 유효성 검사 실패")
		c.JSON(http.StatusBadRequest, gin.H{"error": "입력 데이터가 유효하지 않습니다", "details": err.Error()})
		return
	}

	// 사용자 생성
	user, err := h.userService.CreateUser(&req)
	if err != nil {
		logrus.WithError(err).Error("사용자 생성 실패")
		c.JSON(http.StatusInternalServerError, gin.H{"error": "사용자 생성에 실패했습니다"})
		return
	}

	c.JSON(http.StatusCreated, gin.H{"message": "사용자가 생성되었습니다", "user": user.ToResponse()})
}

// Login 로그인 핸들러
func (h *UserHandler) Login(c *gin.Context) {
	var req models.LoginRequest

	// JSON 바인딩
	if err := c.ShouldBindJSON(&req); err != nil {
		logrus.WithError(err).Error("로그인 요청 바인딩 실패")
		c.JSON(http.StatusBadRequest, gin.H{"error": "잘못된 요청 형식입니다"})
		return
	}

	// 유효성 검사
	if err := h.validator.Struct(req); err != nil {
		logrus.WithError(err).Error("로그인 요청 유효성 검사 실패")
		c.JSON(http.StatusBadRequest, gin.H{"error": "입력 데이터가 유효하지 않습니다"})
		return
	}

	// 사용자 인증
	user, err := h.userService.AuthenticateUser(req.Email, req.Password)
	if err != nil {
		logrus.WithError(err).Error("사용자 인증 실패")
		c.JSON(http.StatusUnauthorized, gin.H{"error": "이메일 또는 비밀번호가 올바르지 않습니다"})
		return
	}

	// JWT 토큰 생성
	token, err := h.jwtManager.GenerateToken(user.ID, user.Email, user.Role)
	if err != nil {
		logrus.WithError(err).Error("JWT 토큰 생성 실패")
		c.JSON(http.StatusInternalServerError, gin.H{"error": "로그인 처리에 실패했습니다"})
		return
	}

	c.JSON(http.StatusOK, gin.H{
		"message": "로그인 성공",
		"token":   token,
		"user":    user.ToResponse(),
	})
}

// GetUsers 사용자 목록 조회 핸들러
func (h *UserHandler) GetUsers(c *gin.Context) {
	users, err := h.userService.GetAllUsers()
	if err != nil {
		logrus.WithError(err).Error("사용자 목록 조회 실패")
		c.JSON(http.StatusInternalServerError, gin.H{"error": "사용자 목록 조회에 실패했습니다"})
		return
	}

	// 응답용 사용자 목록 변환
	var userResponses []*models.UserResponse
	for _, user := range users {
		userResponses = append(userResponses, user.ToResponse())
	}

	c.JSON(http.StatusOK, gin.H{"users": userResponses})
}

// GetUser 특정 사용자 조회 핸들러
func (h *UserHandler) GetUser(c *gin.Context) {
	// URL 파라미터에서 사용자 ID 추출
	idStr := c.Param("id")
	id, err := strconv.ParseUint(idStr, 10, 32)
	if err != nil {
		logrus.WithError(err).Error("잘못된 사용자 ID")
		c.JSON(http.StatusBadRequest, gin.H{"error": "유효하지 않은 사용자 ID입니다"})
		return
	}

	user, err := h.userService.GetUserByID(uint(id))
	if err != nil {
		logrus.WithError(err).Error("사용자 조회 실패")
		c.JSON(http.StatusNotFound, gin.H{"error": "사용자를 찾을 수 없습니다"})
		return
	}

	c.JSON(http.StatusOK, gin.H{"user": user.ToResponse()})
}

// UpdateUser 사용자 정보 수정 핸들러
func (h *UserHandler) UpdateUser(c *gin.Context) {
	// URL 파라미터에서 사용자 ID 추출
	idStr := c.Param("id")
	id, err := strconv.ParseUint(idStr, 10, 32)
	if err != nil {
		logrus.WithError(err).Error("잘못된 사용자 ID")
		c.JSON(http.StatusBadRequest, gin.H{"error": "유효하지 않은 사용자 ID입니다"})
		return
	}

	var req models.UserUpdateRequest

	// JSON 바인딩
	if err := c.ShouldBindJSON(&req); err != nil {
		logrus.WithError(err).Error("사용자 수정 요청 바인딩 실패")
		c.JSON(http.StatusBadRequest, gin.H{"error": "잘못된 요청 형식입니다"})
		return
	}

	// 유효성 검사
	if err := h.validator.Struct(req); err != nil {
		logrus.WithError(err).Error("사용자 수정 요청 유효성 검사 실패")
		c.JSON(http.StatusBadRequest, gin.H{"error": "입력 데이터가 유효하지 않습니다"})
		return
	}

	// 사용자 정보 수정
	user, err := h.userService.UpdateUser(uint(id), &req)
	if err != nil {
		logrus.WithError(err).Error("사용자 정보 수정 실패")
		c.JSON(http.StatusInternalServerError, gin.H{"error": "사용자 정보 수정에 실패했습니다"})
		return
	}

	c.JSON(http.StatusOK, gin.H{"message": "사용자 정보가 수정되었습니다", "user": user.ToResponse()})
}

// DeleteUser 사용자 삭제 핸들러
func (h *UserHandler) DeleteUser(c *gin.Context) {
	// URL 파라미터에서 사용자 ID 추출
	idStr := c.Param("id")
	id, err := strconv.ParseUint(idStr, 10, 32)
	if err != nil {
		logrus.WithError(err).Error("잘못된 사용자 ID")
		c.JSON(http.StatusBadRequest, gin.H{"error": "유효하지 않은 사용자 ID입니다"})
		return
	}

	// 사용자 삭제
	if err := h.userService.DeleteUser(uint(id)); err != nil {
		logrus.WithError(err).Error("사용자 삭제 실패")
		c.JSON(http.StatusInternalServerError, gin.H{"error": "사용자 삭제에 실패했습니다"})
		return
	}

	c.JSON(http.StatusOK, gin.H{"message": "사용자가 삭제되었습니다"})
}
