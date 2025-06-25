package main

import (
	"fmt"
	"sample-intranet-backend/internal/config"
	"sample-intranet-backend/internal/database"
	"sample-intranet-backend/internal/routes"

	"github.com/sirupsen/logrus"
)

func main() {
	// 설정 로드
	cfg, err := config.LoadConfig()
	if err != nil {
		logrus.WithError(err).Fatal("설정 파일 로드 실패")
	}

	// 로거 설정
	setupLogger(cfg)

	// 데이터베이스 초기화
	if err := database.InitDatabase(cfg); err != nil {
		logrus.WithError(err).Fatal("데이터베이스 초기화 실패")
	}

	// 라우터 설정
	router := routes.SetupRoutes(cfg)

	// 서버 시작
	serverAddr := fmt.Sprintf(":%s", cfg.Server.Port)
	logrus.WithField("address", serverAddr).Info("서버 시작")

	if err := router.Run(serverAddr); err != nil {
		logrus.WithError(err).Fatal("서버 시작 실패")
	}
}

// setupLogger 로거 설정
func setupLogger(cfg *config.Config) {
	// 로그 레벨 설정
	level, err := logrus.ParseLevel(cfg.Log.Level)
	if err != nil {
		logrus.WithError(err).Warn("유효하지 않은 로그 레벨, INFO 레벨로 설정")
		level = logrus.InfoLevel
	}
	logrus.SetLevel(level)

	// 로그 형식 설정
	if cfg.Log.Format == "json" {
		logrus.SetFormatter(&logrus.JSONFormatter{})
	} else {
		logrus.SetFormatter(&logrus.TextFormatter{
			FullTimestamp: true,
		})
	}

	logrus.Info("로거 설정 완료")
}
