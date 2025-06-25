package auth

import (
	"time"

	"github.com/golang-jwt/jwt/v4"
	"github.com/sirupsen/logrus"
)

// JWTClaims JWT 토큰에 포함될 클레임
type JWTClaims struct {
	UserID uint   `json:"user_id"`
	Email  string `json:"email"`
	Role   string `json:"role"`
	jwt.RegisteredClaims
}

// JWTManager JWT 토큰 관리자
type JWTManager struct {
	secretKey     string
	tokenDuration time.Duration
}

// NewJWTManager JWT 관리자 생성
func NewJWTManager(secretKey string, tokenDuration time.Duration) *JWTManager {
	return &JWTManager{
		secretKey:     secretKey,
		tokenDuration: tokenDuration,
	}
}

// GenerateToken JWT 토큰 생성
func (j *JWTManager) GenerateToken(userID uint, email, role string) (string, error) {
	claims := JWTClaims{
		UserID: userID,
		Email:  email,
		Role:   role,
		RegisteredClaims: jwt.RegisteredClaims{
			ExpiresAt: jwt.NewNumericDate(time.Now().Add(j.tokenDuration)),
			IssuedAt:  jwt.NewNumericDate(time.Now()),
		},
	}

	token := jwt.NewWithClaims(jwt.SigningMethodHS256, claims)
	tokenString, err := token.SignedString([]byte(j.secretKey))
	if err != nil {
		logrus.WithError(err).Error("JWT 토큰 생성 실패")
		return "", err
	}

	return tokenString, nil
}

// ValidateToken JWT 토큰 검증
func (j *JWTManager) ValidateToken(tokenString string) (*JWTClaims, error) {
	token, err := jwt.ParseWithClaims(tokenString, &JWTClaims{}, func(token *jwt.Token) (interface{}, error) {
		return []byte(j.secretKey), nil
	})

	if err != nil {
		logrus.WithError(err).Error("JWT 토큰 파싱 실패")
		return nil, err
	}

	if !token.Valid {
		logrus.Error("유효하지 않은 JWT 토큰")
		return nil, jwt.ErrTokenMalformed
	}

	claims, ok := token.Claims.(*JWTClaims)
	if !ok {
		logrus.Error("JWT 클레임 타입 변환 실패")
		return nil, jwt.ErrTokenInvalidClaims
	}

	return claims, nil
}
