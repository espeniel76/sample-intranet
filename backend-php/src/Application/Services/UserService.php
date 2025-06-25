<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Domain\User\User;
use App\Domain\User\UserRepository;
use App\Infrastructure\Auth\JwtService;

/**
 * 사용자 비즈니스 로직 서비스
 */
class UserService
{
    private UserRepository $userRepository;
    private JwtService $jwtService;

    public function __construct(UserRepository $userRepository, JwtService $jwtService)
    {
        $this->userRepository = $userRepository;
        $this->jwtService = $jwtService;
    }

    /**
     * 사용자 생성 (회원가입)
     */
    public function createUser(array $userData): User
    {
        // 이메일 중복 확인
        if ($this->userRepository->existsByEmail($userData['email'])) {
            throw new \InvalidArgumentException('이미 존재하는 이메일입니다: ' . $userData['email']);
        }

        // 비밀번호 해싱
        $hashedPassword = User::hashPassword($userData['password']);

        // 사용자 생성
        $user = new User(
            0, // ID는 데이터베이스에서 자동 생성
            $userData['email'],
            $hashedPassword,
            $userData['name'],
            $userData['role'] ?? User::ROLE_USER,
            $userData['isActive'] ?? true
        );

        return $this->userRepository->create($user);
    }

    /**
     * 사용자 인증 (로그인)
     */
    public function authenticateUser(string $email, string $password): ?array
    {
        $user = $this->userRepository->findByEmail($email);
        
        if (!$user || !$user->isActive()) {
            return null;
        }

        if (!$user->verifyPassword($password)) {
            return null;
        }

        // JWT 토큰 생성
        return $this->jwtService->generateToken($user);
    }

    /**
     * 모든 활성 사용자 조회
     */
    public function getAllUsers(int $limit = 100, int $offset = 0): array
    {
        return $this->userRepository->findAll($limit, $offset);
    }

    /**
     * ID로 사용자 조회
     */
    public function getUserById(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    /**
     * 이메일로 사용자 조회
     */
    public function getUserByEmail(string $email): ?User
    {
        return $this->userRepository->findByEmail($email);
    }

    /**
     * 사용자 정보 업데이트
     */
    public function updateUser(int $id, array $updateData): User
    {
        $user = $this->userRepository->findById($id);
        
        if (!$user) {
            throw new \InvalidArgumentException('사용자를 찾을 수 없습니다: ' . $id);
        }

        // 업데이트 가능한 필드만 처리
        if (isset($updateData['name'])) {
            $user->setName($updateData['name']);
        }

        if (isset($updateData['role'])) {
            $user->setRole($updateData['role']);
        }

        if (isset($updateData['isActive'])) {
            $user->setActive($updateData['isActive']);
        }

        return $this->userRepository->update($user);
    }

    /**
     * 사용자 삭제
     */
    public function deleteUser(int $id): bool
    {
        $user = $this->userRepository->findById($id);
        
        if (!$user) {
            throw new \InvalidArgumentException('사용자를 찾을 수 없습니다: ' . $id);
        }

        return $this->userRepository->delete($id);
    }

    /**
     * 이름으로 사용자 검색
     */
    public function searchUsersByName(string $name): array
    {
        if (empty(trim($name))) {
            throw new \InvalidArgumentException('검색어를 입력해주세요');
        }

        return $this->userRepository->findByName($name);
    }

    /**
     * 사용자 수 조회
     */
    public function getUserCount(): int
    {
        return $this->userRepository->count();
    }
} 