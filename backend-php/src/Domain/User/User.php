<?php

declare(strict_types=1);

namespace App\Domain\User;

use JsonSerializable;
use DateTime;

/**
 * 사용자 도메인 모델
 */
class User implements JsonSerializable
{
    public const ROLE_USER = 'USER';
    public const ROLE_ADMIN = 'ADMIN';

    private int $id;
    private string $email;
    private string $password;
    private string $name;
    private string $role;
    private bool $isActive;
    private DateTime $createdAt;
    private DateTime $updatedAt;

    public function __construct(
        int $id,
        string $email,
        string $password,
        string $name,
        string $role = self::ROLE_USER,
        bool $isActive = true,
        ?DateTime $createdAt = null,
        ?DateTime $updatedAt = null
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->password = $password;
        $this->name = $name;
        $this->role = $role;
        $this->isActive = $isActive;
        $this->createdAt = $createdAt ?? new DateTime();
        $this->updatedAt = $updatedAt ?? new DateTime();
    }

    // Getters
    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    // Setters
    public function setName(string $name): void
    {
        $this->name = $name;
        $this->updatedAt = new DateTime();
    }

    public function setRole(string $role): void
    {
        if (!in_array($role, [self::ROLE_USER, self::ROLE_ADMIN])) {
            throw new \InvalidArgumentException('Invalid role: ' . $role);
        }
        $this->role = $role;
        $this->updatedAt = new DateTime();
    }

    public function setActive(bool $isActive): void
    {
        $this->isActive = $isActive;
        $this->updatedAt = new DateTime();
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
        $this->updatedAt = new DateTime();
    }

    // 비밀번호 검증
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    // 관리자 권한 확인
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    // JSON 직렬화 (비밀번호 제외)
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'role' => $this->role,
            'isActive' => $this->isActive,
            'createdAt' => $this->createdAt->format('Y-m-d\TH:i:s\Z'),
            'updatedAt' => $this->updatedAt->format('Y-m-d\TH:i:s\Z'),
        ];
    }

    // 비밀번호 해싱
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
} 