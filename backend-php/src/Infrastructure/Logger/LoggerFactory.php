<?php

declare(strict_types=1);

namespace App\Infrastructure\Logger;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Formatter\LineFormatter;
use Psr\Log\LoggerInterface;

/**
 * 로거 팩토리
 */
class LoggerFactory
{
    /**
     * 로거 인스턴스 생성
     */
    public static function create(array $config): LoggerInterface
    {
        $logger = new Logger($config['name']);

        // 로그 레벨 설정
        $level = self::getLogLevel($config['level']);

        // 파일 핸들러 추가
        if (isset($config['path'])) {
            $fileHandler = new StreamHandler($config['path'], $level);
            $fileHandler->setFormatter(self::createFormatter());
            $logger->pushHandler($fileHandler);
        }

        // 시스템 로그 핸들러 추가
        $errorLogHandler = new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, $level);
        $errorLogHandler->setFormatter(self::createFormatter());
        $logger->pushHandler($errorLogHandler);

        return $logger;
    }

    /**
     * 로그 레벨 문자열을 상수로 변환
     */
    private static function getLogLevel(string $level): int
    {
        return match (strtolower($level)) {
            'debug' => Logger::DEBUG,
            'info' => Logger::INFO,
            'notice' => Logger::NOTICE,
            'warning' => Logger::WARNING,
            'error' => Logger::ERROR,
            'critical' => Logger::CRITICAL,
            'alert' => Logger::ALERT,
            'emergency' => Logger::EMERGENCY,
            default => Logger::INFO,
        };
    }

    /**
     * 로그 포맷터 생성
     */
    private static function createFormatter(): LineFormatter
    {
        $dateFormat = 'Y-m-d H:i:s';
        $output = "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";
        
        return new LineFormatter($output, $dateFormat, true, true);
    }
} 