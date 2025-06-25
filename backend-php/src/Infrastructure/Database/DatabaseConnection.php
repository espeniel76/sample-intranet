<?php

declare(strict_types=1);

namespace App\Infrastructure\Database;

use PDO;
use PDOException;

/**
 * 데이터베이스 연결 관리
 */
class DatabaseConnection
{
    private PDO $pdo;
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->connect();
    }

    /**
     * 데이터베이스 연결
     */
    private function connect(): void
    {
        try {
            $dsn = sprintf(
                'pgsql:host=%s;port=%d;dbname=%s;charset=%s',
                $this->config['host'],
                $this->config['port'],
                $this->config['dbname'],
                $this->config['charset']
            );

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => true,
            ];

            $this->pdo = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                $options
            );

            error_log('🗄️  PostgreSQL 데이터베이스 연결 성공');
        } catch (PDOException $e) {
            error_log('❌ 데이터베이스 연결 실패: ' . $e->getMessage());
            throw new \RuntimeException('데이터베이스 연결에 실패했습니다', 0, $e);
        }
    }

    /**
     * PDO 인스턴스 반환
     */
    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    /**
     * 데이터베이스 연결 상태 확인
     */
    public function ping(): bool
    {
        try {
            $this->pdo->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            error_log('데이터베이스 연결 상태 확인 실패: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 트랜잭션 시작
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * 트랜잭션 커밋
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * 트랜잭션 롤백
     */
    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }
} 