<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Container\ContainerInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

class AuthMiddleware
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * 身份验证中间件
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $response = new \Slim\Psr7\Response();
        
        // 获取Authorization标头
        $authHeader = $request->getHeaderLine('Authorization');
        
        // 检查标头格式
        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => '未提供token或格式不正确',
                'code' => 401
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }
        
        $jwt = $matches[1];
        
        // 验证token
        try {
            $settings = $this->container->get('settings');
            $decoded = JWT::decode($jwt, new Key($settings['jwt']['secret'], 'HS256'));
            
            // 将解码后的token添加到请求中
            $request = $request->withAttribute('token', $decoded);
            $request = $request->withAttribute('user_id', $decoded->sub);
            $request = $request->withAttribute('user_roles', $decoded->roles);
            
            // 继续处理请求
            return $handler->handle($request);
            
        } catch (ExpiredException $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Token已过期',
                'code' => 401
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
            
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Token无效: ' . $e->getMessage(),
                'code' => 401
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }
    }
}
