<?php

declare(strict_types=1);

use App\Application\App;
use DI\ContainerBuilder;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

// 환경 변수 로드
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// 컨테이너 설정
$containerBuilder = new ContainerBuilder();

// 설정 로드
$settings = require __DIR__ . '/../src/settings.php';
$containerBuilder->addDefinitions($settings);

// 의존성 주입 설정
$dependencies = require __DIR__ . '/../src/dependencies.php';
$containerBuilder->addDefinitions($dependencies);

// 컨테이너 빌드
$container = $containerBuilder->build();

// 앱 생성
AppFactory::setContainer($container);
$app = AppFactory::create();

// 미들웨어 설정
$middleware = require __DIR__ . '/../src/middleware.php';
$middleware($app);

// 라우트 설정
$routes = require __DIR__ . '/../src/routes.php';
$routes($app);

// 오류 처리
$errorMiddleware = $app->addErrorMiddleware(
    $_ENV['APP_DEBUG'] ?? false,
    true,
    true
);

// 앱 실행
$app->run(); 