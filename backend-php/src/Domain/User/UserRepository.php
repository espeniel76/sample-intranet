<?php

declare(strict_types=1);

namespace App\Domain\User;

/**
 * 사용자 레포지토리 인터페이스
 */
interface UserRepository
{
    /**
     * 사용자 생성
     */
    public function create(User $user): User;

    /**
     * ID로 사용자 조회
     */
    public function findById(int $id): ?User;

    /**
     * 이메일로 사용자 조회
     */
    public function findByEmail(string $email): ?User;

    /**
     * 모든 활성 사용자 조회
     */
    public function findAll(int $limit = 100, int $offset = 0): array;

    /**
     * 이름으로 사용자 검색
     */
    public function findByName(string $name): array;

    /**
     * 사용자 정보 업데이트
     */
    public function update(User $user): User;

    /**
     * 사용자 삭제
     */
    public function delete(int $id): bool;

    /**
     * 활성 사용자 수 조회
     */
    public function count(): int;

    /**
     * 이메일 중복 확인
     */
    public function existsByEmail(string $email): bool;
} 