package middleware

import (
	"net/http"
	"sample-intranet-backend/internal/auth"
	"strings"

	"github.com/gin-gonic/gin"
	"github.com/sirupsen/logrus"
)

// AuthMiddleware JWT 인증 미들웨어
func AuthMiddleware(jwtManager *auth.JWTManager) gin.HandlerFunc {
	return func(c *gin.Context) {
		// Authorization 헤더에서 토큰 추출
		authHeader := c.GetHeader("Authorization")
		if authHeader == "" {
			logrus.Error("Authorization 헤더가 없습니다")
			c.JSON(http.StatusUnauthorized, gin.H{"error": "인증 토큰이 필요합니다"})
			c.Abort()
			return
		}

		// Bearer 토큰 형식 확인
		parts := strings.SplitN(authHeader, " ", 2)
		if len(parts) != 2 || parts[0] != "Bearer" {
			logrus.Error("잘못된 Authorization 헤더 형식")
			c.JSON(http.StatusUnauthorized, gin.H{"error": "잘못된 토큰 형식입니다"})
			c.Abort()
			return
		}

		// 토큰 검증
		claims, err := jwtManager.ValidateToken(parts[1])
		if err != nil {
			logrus.WithError(err).Error("토큰 검증 실패")
			c.JSON(http.StatusUnauthorized, gin.H{"error": "유효하지 않은 토큰입니다"})
			c.Abort()
			return
		}

		// 사용자 정보를 컨텍스트에 저장
		c.Set("user_id", claims.UserID)
		c.Set("user_email", claims.Email)
		c.Set("user_role", claims.Role)

		c.Next()
	}
}

// AdminMiddleware 관리자 권한 확인 미들웨어
func AdminMiddleware() gin.HandlerFunc {
	return func(c *gin.Context) {
		role, exists := c.Get("user_role")
		if !exists {
			logrus.Error("사용자 역할 정보가 없습니다")
			c.JSON(http.StatusUnauthorized, gin.H{"error": "인증이 필요합니다"})
			c.Abort()
			return
		}

		if role != "admin" {
			logrus.Error("관리자 권한이 필요한 요청")
			c.JSON(http.StatusForbidden, gin.H{"error": "관리자 권한이 필요합니다"})
			c.Abort()
			return
		}

		c.Next()
	}
}
