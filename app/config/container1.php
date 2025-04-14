<?php

use Psr\Container\ContainerInterface;
use App\Middleware\CorsMiddleware;
use App\Middleware\AuthMiddleware;
use App\Services\AdsApiService;
use App\Services\AuthService;

return [
    'settings' => [
        'displayErrorDetails' => $_ENV['APP_DEBUG'] === 'true',
        'db' => [
            'driver' => 'mysql',
            'host' => $_ENV['DB_HOST'],
            'port' => $_ENV['DB_PORT'],
            'database' => $_ENV['DB_DATABASE'],
            'username' => $_ENV['DB_USERNAME'],
            'password' => $_ENV['DB_PASSWORD'],
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
        ],
        'jwt' => [
            'secret' => $_ENV['JWT_SECRET'],
            'expiration' => (int)$_ENV['JWT_EXPIRATION'],
        ],
    ],
    
    // PDO实例
    'pdo' => function (ContainerInterface $c) {
        $settings = $c->get('settings')['db'];
        $dsn = sprintf(
            '%s:host=%s;port=%s;dbname=%s;charset=%s',
            $settings['driver'],
            $settings['host'],
            $settings['port'],
            $settings['database'],
            $settings['charset']
        );
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        
        try {
            return new PDO($dsn, $settings['username'], $settings['password'], $options);
        } catch (\PDOException $e) {
            throw new Exception('数据库连接失败: ' . $e->getMessage());
        }
    },
    
    // CORS中间件
    'corsMiddleware' => function (ContainerInterface $c) {
        return new CorsMiddleware();
    },
    
    // 身份验证中间件
    'authMiddleware' => function (ContainerInterface $c) {
        return new AuthMiddleware($c);
    },
    
// 身份验证服务
'authService' => function (ContainerInterface $c) {
    return new \App\Services\AuthService($c);
},

    
    // ADS API服务
    'adsApiService' => function (ContainerInterface $c) {
        return new AdsApiService($c);
    },
];
