<?php

declare(strict_types=1);

use App\Application\Controllers\UserController;
use App\Application\Controllers\HealthController;
use App\Application\Middleware\AuthMiddleware;
use App\Application\Middleware\AdminMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    // 헬스체크
    $app->get('/health', HealthController::class . ':health');
    $app->get('/api/v1/health', HealthController::class . ':health');

    // 루트 엔드포인트
    $app->get('/', HealthController::class . ':info');

    // 인증 관련 (공개)
    $app->group('/api/v1/auth', function (RouteCollectorProxy $group) {
        $group->post('/register', UserController::class . ':register');
        $group->post('/login', UserController::class . ':login');
    });

    // 사용자 관리 (인증 필요)
    $app->group('/api/v1/users', function (RouteCollectorProxy $group) {
        $group->get('', UserController::class . ':getUsers');
        $group->get('/search', UserController::class . ':searchUsers');
        $group->get('/{id:[0-9]+}', UserController::class . ':getUser');
        $group->put('/{id:[0-9]+}', UserController::class . ':updateUser');
    })->add(AuthMiddleware::class);

    // 관리자 전용
    $app->group('/api/v1/admin', function (RouteCollectorProxy $group) {
        $group->delete('/users/{id:[0-9]+}', UserController::class . ':deleteUser');
    })->add(AdminMiddleware::class)->add(AuthMiddleware::class);
}; 