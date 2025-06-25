<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use App\Infrastructure\Auth\JwtService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * JWT 인증 미들웨어
 */
class AuthMiddleware implements MiddlewareInterface
{
    private JwtService $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        try {
            $authHeader = $request->getHeaderLine('Authorization');
            
            if (empty($authHeader)) {
                return $this->unauthorizedResponse('Authorization 헤더가 없습니다');
            }
            
            $token = $this->jwtService->extractToken($authHeader);
            $userInfo = $this->jwtService->getTokenUser($token);
            
            // 요청 객체에 사용자 정보 추가
            $request = $request->withAttribute('user', $userInfo);
            
            return $handler->handle($request);
            
        } catch (\InvalidArgumentException $e) {
            return $this->unauthorizedResponse($e->getMessage());
        } catch (\Exception $e) {
            return $this->unauthorizedResponse('인증에 실패했습니다');
        }
    }

    /**
     * 인증 실패 응답 생성
     */
    private function unauthorizedResponse(string $message): Response
    {
        $response = new \Slim\Psr7\Response();
        $errorData = [
            'error' => '인증 실패',
            'detail' => $message
        ];
        
        $response->getBody()->write(json_encode($errorData));
        return $response
            ->withStatus(401)
            ->withHeader('Content-Type', 'application/json');
    }
} 