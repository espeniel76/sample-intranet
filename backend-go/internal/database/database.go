package database

import (
	"fmt"
	"sample-intranet-backend/internal/config"
	"sample-intranet-backend/internal/models"

	"github.com/sirupsen/logrus"
	"gorm.io/driver/postgres"
	"gorm.io/gorm"
)

// DB 전역 데이터베이스 인스턴스
var DB *gorm.DB

// InitDatabase 데이터베이스 연결 및 초기화
func InitDatabase(cfg *config.Config) error {
	// PostgreSQL 연결 문자열 생성
	dsn := fmt.Sprintf("host=%s user=%s password=%s dbname=%s port=%d sslmode=%s",
		cfg.Database.Host,
		cfg.Database.User,
		cfg.Database.Password,
		cfg.Database.DBName,
		cfg.Database.Port,
		cfg.Database.SSLMode,
	)

	// GORM으로 데이터베이스 연결
	var err error
	DB, err = gorm.Open(postgres.Open(dsn), &gorm.Config{})
	if err != nil {
		logrus.WithError(err).Error("데이터베이스 연결 실패")
		return err
	}

	logrus.Info("데이터베이스 연결 성공")

	// 테이블 자동 마이그레이션
	if err := migrateModels(); err != nil {
		logrus.WithError(err).Error("데이터베이스 마이그레이션 실패")
		return err
	}

	logrus.Info("데이터베이스 마이그레이션 완료")
	return nil
}

// migrateModels 모델 마이그레이션 실행
func migrateModels() error {
	return DB.AutoMigrate(
		&models.User{},
	)
}

// GetDB 데이터베이스 인스턴스 반환
func GetDB() *gorm.DB {
	return DB
}
