<?php

declare(strict_types=1);

namespace App\Infrastructure\User;

use App\Domain\User\User;
use App\Domain\User\UserRepository;
use App\Infrastructure\Database\DatabaseConnection;
use PDO;
use DateTime;

/**
 * PDO를 사용한 사용자 레포지토리 구현
 */
class PdoUserRepository implements UserRepository
{
    private PDO $pdo;

    public function __construct(DatabaseConnection $database)
    {
        $this->pdo = $database->getConnection();
    }

    public function create(User $user): User
    {
        $sql = "
            INSERT INTO users (email, password, name, role, is_active, created_at, updated_at)
            VALUES (:email, :password, :name, :role, :is_active, NOW(), NOW())
            RETURNING id, created_at, updated_at
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
            'name' => $user->getName(),
            'role' => $user->getRole(),
            'is_active' => $user->isActive() ? 1 : 0,
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return new User(
            $result['id'],
            $user->getEmail(),
            $user->getPassword(),
            $user->getName(),
            $user->getRole(),
            $user->isActive(),
            new DateTime($result['created_at']),
            new DateTime($result['updated_at'])
        );
    }

    public function findById(int $id): ?User
    {
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row ? $this->mapRowToUser($row) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row ? $this->mapRowToUser($row) : null;
    }

    public function findAll(int $limit = 100, int $offset = 0): array
    {
        $sql = "
            SELECT * FROM users 
            WHERE is_active = true 
            ORDER BY created_at DESC 
            LIMIT :limit OFFSET :offset
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return array_map([$this, 'mapRowToUser'], $rows);
    }

    public function findByName(string $name): array
    {
        $sql = "
            SELECT * FROM users 
            WHERE is_active = true AND name ILIKE :name 
            ORDER BY created_at DESC
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['name' => '%' . $name . '%']);
        
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return array_map([$this, 'mapRowToUser'], $rows);
    }

    public function update(User $user): User
    {
        $sql = "
            UPDATE users 
            SET name = :name, role = :role, is_active = :is_active, updated_at = NOW()
            WHERE id = :id
            RETURNING updated_at
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id' => $user->getId(),
            'name' => $user->getName(),
            'role' => $user->getRole(),
            'is_active' => $user->isActive() ? 1 : 0,
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return new User(
            $user->getId(),
            $user->getEmail(),
            $user->getPassword(),
            $user->getName(),
            $user->getRole(),
            $user->isActive(),
            $user->getCreatedAt(),
            new DateTime($result['updated_at'])
        );
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        return $stmt->rowCount() > 0;
    }

    public function count(): int
    {
        $sql = "SELECT COUNT(*) FROM users WHERE is_active = true";
        $stmt = $this->pdo->query($sql);
        
        return (int) $stmt->fetchColumn();
    }

    public function existsByEmail(string $email): bool
    {
        $sql = "SELECT COUNT(*) FROM users WHERE email = :email";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        
        return $stmt->fetchColumn() > 0;
    }

    /**
     * 데이터베이스 행을 User 객체로 매핑
     */
    private function mapRowToUser(array $row): User
    {
        return new User(
            (int) $row['id'],
            $row['email'],
            $row['password'],
            $row['name'],
            $row['role'],
            (bool) $row['is_active'],
            new DateTime($row['created_at']),
            new DateTime($row['updated_at'])
        );
    }
} 