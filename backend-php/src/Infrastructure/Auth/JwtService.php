<?php

declare(strict_types=1);

namespace App\Infrastructure\Auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use App\Domain\User\User;

/**
 * JWT 인증 서비스
 */
class JwtService
{
    private string $secret;
    private string $algorithm;
    private int $expiresIn;

    public function __construct(array $config)
    {
        $this->secret = $config['secret'];
        $this->algorithm = $config['algorithm'];
        $this->expiresIn = $config['expires_in'];
    }

    /**
     * JWT 토큰 생성
     */
    public function generateToken(User $user): array
    {
        $now = time();
        $payload = [
            'iss' => 'sample-intranet-php',  // 발급자
            'sub' => (string) $user->getId(), // 사용자 ID
            'iat' => $now,                   // 발급 시간
            'exp' => $now + $this->expiresIn, // 만료 시간
            'userId' => $user->getId(),
            'email' => $user->getEmail(),
            'role' => $user->getRole(),
        ];

        $token = JWT::encode($payload, $this->secret, $this->algorithm);

        return [
            'accessToken' => $token,
            'tokenType' => 'bearer',
            'expiresIn' => $this->expiresIn . 's',
            'user' => $user->jsonSerialize(),
        ];
    }

    /**
     * JWT 토큰 검증 및 디코딩
     */
    public function verifyToken(string $token): array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, $this->algorithm));
            return (array) $decoded;
        } catch (ExpiredException $e) {
            throw new \InvalidArgumentException('토큰이 만료되었습니다');
        } catch (SignatureInvalidException $e) {
            throw new \InvalidArgumentException('유효하지 않은 토큰입니다');
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('토큰 검증에 실패했습니다: ' . $e->getMessage());
        }
    }

    /**
     * Authorization 헤더에서 토큰 추출
     */
    public function extractToken(string $authHeader): string
    {
        if (empty($authHeader)) {
            throw new \InvalidArgumentException('Authorization 헤더가 없습니다');
        }

        if (!str_starts_with($authHeader, 'Bearer ')) {
            throw new \InvalidArgumentException('잘못된 인증 형식입니다');
        }

        return substr($authHeader, 7); // 'Bearer ' 제거
    }

    /**
     * 토큰에서 사용자 정보 추출
     */
    public function getTokenUser(string $token): array
    {
        $payload = $this->verifyToken($token);
        
        return [
            'userId' => $payload['userId'],
            'email' => $payload['email'],
            'role' => $payload['role'],
        ];
    }

    /**
     * 토큰 만료 시간 반환 (초)
     */
    public function getExpiresIn(): int
    {
        return $this->expiresIn;
    }
} 