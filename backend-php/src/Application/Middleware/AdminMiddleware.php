<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * 관리자 권한 확인 미들웨어
 */
class AdminMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        $user = $request->getAttribute('user');
        
        if (!$user) {
            return $this->forbiddenResponse('인증이 필요합니다');
        }
        
        if ($user['role'] !== User::ROLE_ADMIN) {
            return $this->forbiddenResponse('관리자 권한이 필요합니다');
        }
        
        return $handler->handle($request);
    }

    /**
     * 권한 부족 응답 생성
     */
    private function forbiddenResponse(string $message): Response
    {
        $response = new \Slim\Psr7\Response();
        $errorData = [
            'error' => '권한 부족',
            'detail' => $message
        ];
        
        $response->getBody()->write(json_encode($errorData));
        return $response
            ->withStatus(403)
            ->withHeader('Content-Type', 'application/json');
    }
} 