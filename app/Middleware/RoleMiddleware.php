<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;

class RoleMiddleware
{
    private $roles;

    public function __construct(array $roles)
    {
        $this->roles = $roles;
    }

    /**
     * 角色验证中间件
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $response = new \Slim\Psr7\Response();
        
        // 获取用户角色
        $userRoles = $request->getAttribute('user_roles', []);
        
        // 检查用户是否有所需的角色
        $hasRole = false;
        foreach ($userRoles as $role) {
            if (in_array($role, $this->roles)) {
                $hasRole = true;
                break;
            }
        }
        
        if (!$hasRole) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => '没有权限执行此操作',
                'code' => 403
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }
        
        return $handler->handle($request);
    }
}
