<?php

use Psr\Container\ContainerInterface;
use App\Middleware\CorsMiddleware;
use App\Middleware\AuthMiddleware;
use App\Services\AdsApiService;
use App\Services\AuthService;

return [
    'settings' => [
        'displayErrorDetails' => true, // 开发环境设为true
        'db' => [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => '8889', // MAMP默认MySQL端口
            'database' => 'ads_rsoc_system',
            'username' => 'root',
            'password' => 'root', // MAMP默认密码
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
        ],
        'jwt' => [
            'secret' => 'your-jwt-secret-key-for-development',
            'expiration' => 86400, // 24小时
        ],
    ],
    
    // PDO实例 - 使用备用认证
    'pdo' => function (ContainerInterface $c) {
        try {
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
            
            return new PDO($dsn, $settings['username'], $settings['password'], $options);
        } catch (\PDOException $e) {
            // 仅记录错误，不中断程序执行
            error_log('数据库连接失败: ' . $e->getMessage());
            return null;
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
        return new AuthService($c);
    },
    
    // ADS API服务
    'adsApiService' => function (ContainerInterface $c) {
        return new AdsApiService($c);
    },
];
