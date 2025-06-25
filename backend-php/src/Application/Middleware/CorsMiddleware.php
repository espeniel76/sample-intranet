<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * CORS 미들웨어
 */
class CorsMiddleware implements MiddlewareInterface
{
    private array $allowedOrigins;

    public function __construct(array $settings)
    {
        $this->allowedOrigins = $settings['cors']['origin'] ?? ['*'];
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $response = $handler->handle($request);
        
        return $this->addCorsHeaders($response, $request);
    }

    /**
     * CORS 헤더 추가
     */
    private function addCorsHeaders(Response $response, Request $request): Response
    {
        $origin = $request->getHeaderLine('Origin');
        
        // Origin 확인
        if ($this->isAllowedOrigin($origin)) {
            $response = $response->withHeader('Access-Control-Allow-Origin', $origin);
        } elseif (in_array('*', $this->allowedOrigins)) {
            $response = $response->withHeader('Access-Control-Allow-Origin', '*');
        }
        
        $response = $response
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Access-Control-Max-Age', '86400');
        
        return $response;
    }

    /**
     * 허용된 Origin인지 확인
     */
    private function isAllowedOrigin(string $origin): bool
    {
        return in_array($origin, $this->allowedOrigins);
    }
} 