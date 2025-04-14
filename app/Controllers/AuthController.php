<?php

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Container\ContainerInterface;
use App\Services\AuthService;

class AuthController
{
    private $container;
    private $authService;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->authService = $container->get('authService');
    }

    /**
     * 用户登录
     */
    public function login(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        
        // 验证请求数据
        if (!isset($data['username']) || !isset($data['password'])) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => '用户名和密码不能为空',
                'code' => 400
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        
        // 使用开发模式认证进行测试
        $result = $this->authService->authenticateDevelopment($data['username'], $data['password']);
        
        // 验证用户凭据
        $result = $this->authService->authenticate($data['username'], $data['password']);
        
        if (!$result['success']) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => $result['message'],
                'code' => 401
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }
        
        // 返回token和用户信息
        $response->getBody()->write(json_encode([
            'success' => true,
            'data' => [
                'token' => $result['token'],
                'user' => $result['user']
            ]
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    /**
     * 获取当前用户信息
     */
    public function getUser(Request $request, Response $response): Response
    {
        $userId = $request->getAttribute('user_id');
        
        // 获取用户信息
        $user = $this->authService->getUserById($userId);
        
        if (!$user) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => '用户不存在',
                'code' => 404
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
        
        // 移除敏感信息
        unset($user['password']);
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'data' => $user
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    /**
     * 用户退出登录
     */
    public function logout(Request $request, Response $response): Response
    {
        // 在无状态API中，客户端需要处理token的删除
        // 服务器端可以将token加入黑名单，这里简化处理
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => '成功退出登录'
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
