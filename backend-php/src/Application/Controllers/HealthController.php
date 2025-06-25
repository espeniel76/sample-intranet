<?php

declare(strict_types=1);

namespace App\Application\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * 헬스체크 컨트롤러
 */
class HealthController
{
    /**
     * 헬스체크 엔드포인트
     */
    public function health(Request $request, Response $response): Response
    {
        $healthData = [
            'status' => 'healthy',
            'timestamp' => time(),
            'app_name' => 'Sample Intranet Backend PHP'
        ];
        
        $response->getBody()->write(json_encode($healthData));
        return $response;
    }

    /**
     * 애플리케이션 정보 엔드포인트
     */
    public function info(Request $request, Response $response): Response
    {
        $infoData = [
            'message' => 'Sample Intranet Backend (PHP) 🐘',
            'version' => '1.0.0',
            'docs' => '/api/v1/health',
            'endpoints' => [
                'health' => 'GET /health',
                'register' => 'POST /api/v1/auth/register',
                'login' => 'POST /api/v1/auth/login',
                'users' => 'GET /api/v1/users',
                'userById' => 'GET /api/v1/users/{id}',
                'updateUser' => 'PUT /api/v1/users/{id}',
                'deleteUser' => 'DELETE /api/v1/admin/users/{id}',
                'searchUsers' => 'GET /api/v1/users/search?name=검색어'
            ]
        ];
        
        $response->getBody()->write(json_encode($infoData));
        return $response;
    }
} 