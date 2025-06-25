<?php

declare(strict_types=1);

use App\Infrastructure\Database\DatabaseConnection;
use App\Infrastructure\Logger\LoggerFactory;
use App\Domain\User\UserRepository;
use App\Infrastructure\User\PdoUserRepository;
use App\Application\Services\UserService;
use App\Infrastructure\Auth\JwtService;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return [
    // 로거
    LoggerInterface::class => function (ContainerInterface $c) {
        $settings = $c->get('settings')['logger'];
        return LoggerFactory::create($settings);
    },

    // 데이터베이스 연결
    DatabaseConnection::class => function (ContainerInterface $c) {
        $settings = $c->get('settings')['database'];
        return new DatabaseConnection($settings);
    },

    // 사용자 레포지토리
    UserRepository::class => function (ContainerInterface $c) {
        return new PdoUserRepository($c->get(DatabaseConnection::class));
    },

    // JWT 서비스
    JwtService::class => function (ContainerInterface $c) {
        $settings = $c->get('settings')['jwt'];
        return new JwtService($settings);
    },

    // 사용자 서비스
    UserService::class => function (ContainerInterface $c) {
        return new UserService(
            $c->get(UserRepository::class),
            $c->get(JwtService::class)
        );
    },
]; 