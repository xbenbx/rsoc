<?php

use Slim\Routing\RouteCollectorProxy;
use App\Controllers\AuthController;
use App\Controllers\RsocController;
use App\Controllers\KeywordController;
use App\Controllers\ReportController;
use App\Controllers\UserController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;

// 添加根路由 - 显示欢迎页面
$app->get('/', function ($request, $response) {
    $html = <<<HTML
    <!DOCTYPE html>
    <html>
    <head>
        <title>ADS.COM RSOC Management System</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                max-width: 800px;
                margin: 0 auto;
                padding: 20px;
            }
            h1 {
                color: #333;
                border-bottom: 1px solid #eee;
                padding-bottom: 10px;
            }
            .api-info {
                background: #f5f5f5;
                padding: 15px;
                border-radius: 5px;
                margin: 20px 0;
            }
            ul {
                padding-left: 20px;
            }
            a {
                color: #0066cc;
                text-decoration: none;
            }
            a:hover {
                text-decoration: underline;
            }
        </style>
    </head>
    <body>
        <h1>ADS.COM RSOC Management System API</h1>
        <p>Welcome to the ADS.COM RSOC Management System API Server.</p>
        
        <div class="api-info">
            <h2>Available Endpoints:</h2>
            <ul>
                <li><a href="/test">/test</a> - Test API connection</li>
                <li><code>/api/auth/login</code> - Authentication endpoint (POST)</li>
                <li><code>/api/rsoc</code> - RSOC management</li>
                <li><code>/api/keywords</code> - Keywords management</li>
                <li><code>/api/reports/dashboard</code> - Dashboard data</li>
            </ul>
        </div>
        
        <p>The API server is running correctly. To use the full system, please ensure the frontend application is also running.</p>
        
        <p><strong>Server Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
    </body>
    </html>
    HTML;
    
    $response->getBody()->write($html);
    return $response->withHeader('Content-Type', 'text/html');
});

// 添加测试路由
$app->get('/test', function ($request, $response) {
    $data = [
        'success' => true,
        'message' => 'API is working correctly',
        'time' => date('Y-m-d H:i:s')
    ];
    
    $payload = json_encode($data);
    $response->getBody()->write($payload);
    
    return $response->withHeader('Content-Type', 'application/json');
});

// 路由调试工具
$app->get('/debug/routes', function ($request, $response) use ($app) {
    $routes = $app->getRouteCollector()->getRoutes();
    $routeDetails = [];
    
    foreach ($routes as $route) {
        $pattern = $route->getPattern();
        $methods = $route->getMethods();
        $routeDetails[] = [
            'pattern' => $pattern,
            'methods' => $methods
        ];
    }
    
    $payload = json_encode($routeDetails, JSON_PRETTY_PRINT);
    $response->getBody()->write($payload);
    
    return $response->withHeader('Content-Type', 'application/json');
});

// 公共路由
$app->post('/api/auth/login', [AuthController::class, 'login']);

// 需要身份验证的路由
$app->group('/api', function (RouteCollectorProxy $group) {
    // 用户相关
    $group->get('/auth/user', [AuthController::class, 'getUser']);
    $group->post('/auth/logout', [AuthController::class, 'logout']);
    
    // RSOC管理
    $group->get('/rsoc', [RsocController::class, 'index']);
    $group->post('/rsoc', [RsocController::class, 'store']);
    $group->get('/rsoc/{id}', [RsocController::class, 'show']);
    $group->put('/rsoc/{id}', [RsocController::class, 'update']);
    $group->delete('/rsoc/{id}', [RsocController::class, 'delete']);
    $group->get('/rsoc/{id}/performance', [RsocController::class, 'getPerformance']);
    
    // 关键词管理
    $group->get('/keywords', [KeywordController::class, 'index']);
    $group->post('/keywords', [KeywordController::class, 'store']);
    $group->get('/keywords/{id}', [KeywordController::class, 'show']);
    $group->put('/keywords/{id}', [KeywordController::class, 'update']);
    $group->delete('/keywords/{id}', [KeywordController::class, 'delete']);
    
    // 报表
    $group->get('/reports/dashboard', [ReportController::class, 'dashboard']);
    $group->get('/reports/performance', [ReportController::class, 'performance']);
    $group->get('/reports/keywords', [ReportController::class, 'keywords']);
    $group->get('/reports/revenue', [ReportController::class, 'revenue']);
    
    // 用户管理 (仅管理员)
    $group->group('/users', function (RouteCollectorProxy $group) {
        $group->get('', [UserController::class, 'index']);
        $group->post('', [UserController::class, 'store']);
        $group->get('/{id}', [UserController::class, 'show']);
        $group->put('/{id}', [UserController::class, 'update']);
        $group->delete('/{id}', [UserController::class, 'delete']);
    })->add(new RoleMiddleware(['admin']));
    
})->add($app->getContainer()->get('authMiddleware'));
