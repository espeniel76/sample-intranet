package routes

import (
	"sample-intranet-backend/internal/auth"
	"sample-intranet-backend/internal/config"
	"sample-intranet-backend/internal/database"
	"sample-intranet-backend/internal/handlers"
	"sample-intranet-backend/internal/middleware"
	"sample-intranet-backend/internal/repositories"
	"sample-intranet-backend/internal/services"
	"time"

	"github.com/gin-gonic/gin"
	"github.com/sirupsen/logrus"
)

// SetupRoutes 라우팅 설정
func SetupRoutes(cfg *config.Config) *gin.Engine {
	// Gin 모드 설정
	gin.SetMode(cfg.Server.Mode)

	router := gin.Default()

	// 미들웨어 설정
	router.Use(gin.Logger())
	router.Use(gin.Recovery())

	// CORS 미들웨어 (개발 환경)
	router.Use(func(c *gin.Context) {
		c.Header("Access-Control-Allow-Origin", "*")
		c.Header("Access-Control-Allow-Methods", "GET, POST, PUT, DELETE, OPTIONS")
		c.Header("Access-Control-Allow-Headers", "Content-Type, Authorization")

		if c.Request.Method == "OPTIONS" {
			c.AbortWithStatus(204)
			return
		}

		c.Next()
	})

	// 의존성 초기화
	db := database.GetDB()
	jwtManager := auth.NewJWTManager(
		cfg.JWT.Secret,
		time.Duration(cfg.JWT.ExpiresHours)*time.Hour,
	)

	// 레포지토리 초기화
	userRepo := repositories.NewUserRepository(db)

	// 서비스 초기화
	userService := services.NewUserService(userRepo)

	// 핸들러 초기화
	userHandler := handlers.NewUserHandler(userService, jwtManager)

	// 헬스체크 엔드포인트
	router.GET("/health", func(c *gin.Context) {
		c.JSON(200, gin.H{
			"status":    "healthy",
			"timestamp": time.Now().Unix(),
		})
	})

	// API 라우트 그룹
	api := router.Group("/api/v1")
	{
		// 인증이 필요없는 라우트
		auth := api.Group("/auth")
		{
			auth.POST("/login", userHandler.Login)
			auth.POST("/register", userHandler.CreateUser)
		}

		// 인증이 필요한 라우트
		protected := api.Group("/")
		protected.Use(middleware.AuthMiddleware(jwtManager))
		{
			// 사용자 관련 라우트
			users := protected.Group("/users")
			{
				users.GET("", userHandler.GetUsers)       // 사용자 목록 조회
				users.GET("/:id", userHandler.GetUser)    // 특정 사용자 조회
				users.PUT("/:id", userHandler.UpdateUser) // 사용자 정보 수정
			}

			// 관리자 전용 라우트
			admin := protected.Group("/admin")
			admin.Use(middleware.AdminMiddleware())
			{
				admin.DELETE("/users/:id", userHandler.DeleteUser) // 사용자 삭제
			}
		}
	}

	logrus.Info("라우팅 설정 완료")
	return router
}
