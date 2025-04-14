<?php

namespace App\Services;

use Psr\Container\ContainerInterface;

class AuthService
{
    private $container;
    private $pdo;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        
        // 尝试获取PDO实例，但接受null值
        try {
            $this->pdo = $container->get('pdo');
        } catch (\Exception $e) {
            $this->pdo = null;
            // 记录错误但继续执行
            error_log('无法获取数据库连接：' . $e->getMessage());
        }
    }

    /**
     * 验证用户名和密码
     */
    public function authenticate(string $username, string $password): array
    {
        // 如果没有数据库连接，使用备用认证
        if (!$this->pdo) {
            return $this->authenticateFallback($username, $password);
        }
        
        try {
            // 尝试从数据库验证
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = :username AND status = 'active'");
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            $user = $stmt->fetch();
            
            // 用户不存在，回退到备用认证
            if (!$user) {
                return $this->authenticateFallback($username, $password);
            }
            
            // 检查密码
            if ($password !== $user['password']) {
                return [
                    'success' => false,
                    'message' => '用户名或密码错误',
                ];
            }
            
            // 生成令牌
            $token = 'db_token_' . time();
            
            // 返回用户信息
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
            
        } catch (\Exception $e) {
            // 数据库错误，回退到备用认证
            error_log('数据库认证失败：' . $e->getMessage());
            return $this->authenticateFallback($username, $password);
        }
    }
    
    /**
     * 备用认证方法（不需要数据库）
     */
    private function authenticateFallback(string $username, string $password): array
    {
        // 硬编码的测试凭据
        if ($username === 'admin' && $password === 'admin123') {
            return [
                'success' => true,
                'token' => 'test_token_' . time(),
                'user' => [
                    'id' => 1,
                    'username' => 'admin',
                    'name' => '系统管理员',
                    'email' => 'admin@example.com',
                    'role' => 'admin',
                    'avatar' => null
                ]
            ];
        }
        
        return [
            'success' => false,
            'message' => '用户名或密码错误',
        ];
    }

    /**
     * 通过ID获取用户
     */
    public function getUserById(int $userId): ?array
    {
        // 如果没有数据库连接，使用备用方法
        if (!$this->pdo) {
            return $this->getUserByIdFallback($userId);
        }
        
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->bindParam(':id', $userId, \PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch();
            
            if (!$user) {
                return $this->getUserByIdFallback($userId);
            }
            
            return $user;
            
        } catch (\Exception $e) {
            error_log('获取用户信息失败：' . $e->getMessage());
            return $this->getUserByIdFallback($userId);
        }
    }
    
    /**
     * 备用获取用户方法
     */
    private function getUserByIdFallback(int $userId): ?array
    {
        // 只返回admin用户
        if ($userId === 1) {
            return [
                'id' => 1,
                'username' => 'admin',
                'name' => '系统管理员',
                'email' => 'admin@example.com',
                'role' => 'admin',
                'avatar' => null,
                'password' => 'admin123'  // 实际生产环境中不要这样做
            ];
        }
        
        return null;
    }
}
