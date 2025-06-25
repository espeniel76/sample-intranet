package config

import (
	"github.com/sirupsen/logrus"
	"github.com/spf13/viper"
)

// Config 애플리케이션 전체 설정 구조체
type Config struct {
	Server   ServerConfig   `mapstructure:"server"`
	Database DatabaseConfig `mapstructure:"database"`
	JWT      JWTConfig      `mapstructure:"jwt"`
	Session  SessionConfig  `mapstructure:"session"`
	Log      LogConfig      `mapstructure:"log"`
}

// ServerConfig 서버 설정
type ServerConfig struct {
	Port string `mapstructure:"port"`
	Mode string `mapstructure:"mode"`
}

// DatabaseConfig 데이터베이스 설정
type DatabaseConfig struct {
	Host     string `mapstructure:"host"`
	Port     int    `mapstructure:"port"`
	User     string `mapstructure:"user"`
	Password string `mapstructure:"password"`
	DBName   string `mapstructure:"dbname"`
	SSLMode  string `mapstructure:"sslmode"`
}

// JWTConfig JWT 설정
type JWTConfig struct {
	Secret       string `mapstructure:"secret"`
	ExpiresHours int    `mapstructure:"expires_hours"`
}

// SessionConfig 세션 설정
type SessionConfig struct {
	Secret string `mapstructure:"secret"`
	Name   string `mapstructure:"name"`
	MaxAge int    `mapstructure:"max_age"`
}

// LogConfig 로깅 설정
type LogConfig struct {
	Level  string `mapstructure:"level"`
	Format string `mapstructure:"format"`
}

// LoadConfig 설정 파일을 로드하여 Config 구조체로 반환
func LoadConfig() (*Config, error) {
	viper.SetConfigName("config")
	viper.SetConfigType("yaml")
	viper.AddConfigPath("./config")
	viper.AddConfigPath(".")

	// 환경 변수 자동 바인딩 활성화
	viper.AutomaticEnv()

	// 환경 변수 명시적 바인딩 (Docker 환경용)
	viper.BindEnv("database.host", "DATABASE_HOST")
	viper.BindEnv("database.port", "DATABASE_PORT")
	viper.BindEnv("database.user", "DATABASE_USER")
	viper.BindEnv("database.password", "DATABASE_PASSWORD")
	viper.BindEnv("database.dbname", "DATABASE_DBNAME")
	viper.BindEnv("database.sslmode", "DATABASE_SSLMODE")

	// 설정 파일 읽기
	if err := viper.ReadInConfig(); err != nil {
		logrus.WithError(err).Error("설정 파일 읽기 실패")
		return nil, err
	}

	var config Config
	if err := viper.Unmarshal(&config); err != nil {
		logrus.WithError(err).Error("설정 파일 언마샬링 실패")
		return nil, err
	}

	// 환경 변수 확인 로그 (디버깅용)
	logrus.WithFields(logrus.Fields{
		"database_host": config.Database.Host,
		"database_port": config.Database.Port,
	}).Info("데이터베이스 설정 로드 완료")

	return &config, nil
}
