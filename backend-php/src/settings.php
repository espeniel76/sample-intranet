<?php

declare(strict_types=1);

// 애플리케이션 설정
return [
    'settings' => [
        // 앱 설정
        'app' => [
            'name' => 'Sample Intranet Backend PHP',
            'version' => '1.0.0',
            'debug' => $_ENV['APP_DEBUG'] ?? false,
        ],

        // 데이터베이스 설정
        'database' => [
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => $_ENV['DB_PORT'] ?? 5432,
            'dbname' => $_ENV['DB_NAME'] ?? 'sample_intranet',
            'username' => $_ENV['DB_USERNAME'] ?? 'postgres',
            'password' => $_ENV['DB_PASSWORD'] ?? 'password',
            'charset' => 'utf8',
        ],

        // JWT 설정
        'jwt' => [
            'secret' => $_ENV['JWT_SECRET'] ?? 'your-super-secret-jwt-key-change-this-in-production-php',
            'algorithm' => 'HS256',
            'expires_in' => $_ENV['JWT_EXPIRES_IN'] ?? 86400, // 24시간
        ],

        // CORS 설정
        'cors' => [
            'origin' => explode(',', $_ENV['CORS_ORIGINS'] ?? '*'),
        ],

        // 로거 설정
        'logger' => [
            'name' => 'slim-app',
            'path' => __DIR__ . '/../logs/app.log',
            'level' => $_ENV['LOG_LEVEL'] ?? 'info',
        ],
    ],
]; 