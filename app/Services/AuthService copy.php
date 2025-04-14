<?php

namespace App\Services;

use Psr\Container\ContainerInterface;
use Firebase\JWT\JWT;

class AuthService
{
    private $container;
    private $pdo;
    private $jwtSettings;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->pdo = $container->get('pdo');
        $this->jwtSettings = $container->get('settings')['jwt'];
    }

    /**
     * 验证用户名和密码
     */
    public function authenticate(string $username, string $password): array
    {
        try {
            // 查询用户
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = :username AND status = 'active'");
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            $user = $stmt->fetch();
            
            // 用户不存在
            if (!$user) {
                return [
                    'success' => false,
                    'message' => '用户名或密码错误',
                ];
            }
            
            // 验证密码
            // 注意：在实际项目中，密码应该经过哈希处理并使用password_verify()验证
            // 这里为了简化，假设密码是直接存储的
            $isPasswordValid = $password === $user['password'] || 
                               (function_exists('password_verify') && password_verify($password, $user['password']));
            
            if (!$isPasswordValid) {
                return [
                    'success' => false,
                    'message' => '用户名或密码错误',
                ];
            }
            
            // 生成JWT token
            $token = $this->generateToken($user);
            
            // 返回成功结果
            return [
                'success' => true,
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                    'avatar' => $user['avatar']
                ]
            ];
            
        } catch (\PDOException $e) {
            return [
                'success' => false,
                'message' => '认证过程发生错误: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * 通过ID获取用户信息
     */
    public function getUserById(int $userId): ?array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->bindParam(':id', $userId, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch();
        } catch (\PDOException $e) {
            return null;
        }
    }

    /**
     * 生成JWT Token
     */
    private function generateToken(array $user): string
    {
        $issuedAt = time();
        $expirationTime = $issuedAt + $this->jwtSettings['expiration'];
        
        $payload = [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'sub' => $user['id'],
            'username' => $user['username'],
            'roles' => [$user['role']]
        ];
        
        return JWT::encode($payload, $this->jwtSettings['secret'], 'HS256');
    }

    /**
     * 创建一个简单的临时替代认证
     * 注意：仅用于开发/测试，生产环境应使用真实数据库认证
     */
    public function authenticateDevelopment(string $username, string $password): array
    {
        // 硬编码的测试凭据
        if ($username === 'admin' && $password === 'admin123') {
            $user = [
                'id' => 1,
                'username' => 'admin',
                'name' => '系统管理员',
                'email' => 'admin@example.com',
                'role' => 'admin',
                'avatar' => null
            ];
            
            // 生成JWT token (使用简化版)
            $token = 'dev_token_' . bin2hex(random_bytes(16));
            
            return [
                'success' => true,
                'token' => $token,
                'user' => $user
            ];
        }
        
        return [
            'success' => false,
            'message' => '用户名或密码错误',
        ];
    }
}
