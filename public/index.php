<?php

use Slim\Factory\AppFactory;
use DI\ContainerBuilder;
use Slim\Middleware\ErrorMiddleware;

require __DIR__ . '/../vendor/autoload.php';

// 加载环境变量
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// 创建依赖注入容器
$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions(__DIR__ . '/../app/config/container.php');
$container = $containerBuilder->build();

// 创建应用
AppFactory::setContainer($container);
$app = AppFactory::create();

// 添加中间件
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->add($container->get('corsMiddleware'));

// 添加错误中间件
$errorMiddleware = $app->addErrorMiddleware(
    $_ENV['APP_DEBUG'] === 'true',
    true,
    true
);

// 注册路由
require __DIR__ . '/../app/routes/api.php';

// 运行应用
$app->run();
