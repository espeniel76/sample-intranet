<?php

declare(strict_types=1);

use App\Application\Middleware\CorsMiddleware;
use App\Application\Middleware\AuthMiddleware;
use Slim\App;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

return function (App $app) {
    // JSON 파싱 미들웨어
    $app->addBodyParsingMiddleware();

    // 라우팅 미들웨어
    $app->addRoutingMiddleware();

    // CORS 미들웨어
    $app->add(CorsMiddleware::class);

    // 요청 로깅 미들웨어
    $app->add(function (Request $request, $handler) {
        $start = microtime(true);
        $response = $handler->handle($request);
        $time = microtime(true) - $start;
        
        error_log(sprintf(
            '[%s] %s %s - %d (%.2fms)',
            date('Y-m-d H:i:s'),
            $request->getMethod(),
            $request->getUri()->getPath(),
            $response->getStatusCode(),
            $time * 1000
        ));
        
        return $response;
    });

    // 기본 응답 헤더 미들웨어
    $app->add(function (Request $request, $handler) {
        $response = $handler->handle($request);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('X-Powered-By', 'Sample Intranet PHP Backend');
    });
}; 