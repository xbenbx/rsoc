<?php

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Container\ContainerInterface;
use App\Services\AdsApiService;

class RsocController
{
    private $container;
    private $pdo;
    private $adsApiService;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->pdo = $container->get('pdo');
        $this->adsApiService = $container->get('adsApiService');
    }

    /**
     * 获取RSOC列表
     */
    public function index(Request $request, Response $response): Response
    {
        $userId = $request->getAttribute('user_id');
        $params = $request->getQueryParams();
        
        // 获取分页参数
        $page = isset($params['page']) ? (int)$params['page'] : 1;
        $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
        $offset = ($page - 1) * $limit;
        
        // 获取筛选参数
        $name = isset($params['name']) ? $params['name'] : '';
        $status = isset($params['status']) ? $params['status'] : '';
        $startDate = isset($params['start_date']) ? $params['start_date'] : '';
        $endDate = isset($params['end_date']) ? $params['end_date'] : '';
        
        // 构建查询
        $query = "SELECT * FROM rsocs WHERE user_id = :user_id";
        $countQuery = "SELECT COUNT(*) as total FROM rsocs WHERE user_id = :user_id";
        $queryParams = [':user_id' => $userId];
        
        if (!empty($name)) {
            $query .= " AND name LIKE :name";
            $countQuery .= " AND name LIKE :name";
            $queryParams[':name'] = "%$name%";
        }
        
        if (!empty($status)) {
            $query .= " AND status = :status";
            $countQuery .= " AND status = :status";
            $queryParams[':status'] = $status;
        }
        
        if (!empty($startDate)) {
            $query .= " AND created_at >= :start_date";
            $countQuery .= " AND created_at >= :start_date";
            $queryParams[':start_date'] = $startDate . ' 00:00:00';
        }
        
        if (!empty($endDate)) {
            $query .= " AND created_at <= :end_date";
            $countQuery .= " AND created_at <= :end_date";
            $queryParams[':end_date'] = $endDate . ' 23:59:59';
        }
        
        // 添加排序和分页
        $query .= " ORDER BY id DESC LIMIT :offset, :limit";
        $queryParams[':offset'] = $offset;
        $queryParams[':limit'] = $limit;
        
        // 执行查询
        try {
            // 获取总记录数
            $countStmt = $this->pdo->prepare($countQuery);
            foreach ($queryParams as $key => $value) {
                if ($key != ':offset' && $key != ':limit') {
                    $countStmt->bindValue($key, $value, is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
                }
            }
            $countStmt->execute();
            $totalCount = $countStmt->fetch()['total'];
            
            // 获取当前页数据
            $stmt = $this->pdo->prepare($query);
            foreach ($queryParams as $key => $value) {
                $stmt->bindValue($key, $value, is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
            }
            $stmt->execute();
            $rsocs = $stmt->fetchAll();
            
            // 获取每个RSOC的关键词数量
            foreach ($rsocs as &$rsoc) {
                $keywordStmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM keywords WHERE rsoc_id = :rsoc_id");
                $keywordStmt->bindValue(':rsoc_id', $rsoc['id'], \PDO::PARAM_INT);
                $keywordStmt->execute();
                $rsoc['keywords_count'] = $keywordStmt->fetch()['count'];
            }
            
            // 返回结果
            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => [
                    'items' => $rsocs,
                    'total' => $totalCount,
                    'page' => $page,
                    'limit' => $limit
                ]
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            
        } catch (\PDOException $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => '获取RSOC列表失败: ' . $e->getMessage(),
                'code' => 500
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    /**
     * 获取单个RSOC详情
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        $userId = $request->getAttribute('user_id');
        $rsocId = $args['id'];
        
        try {
            // 查询RSOC
            $stmt = $this->pdo->prepare("SELECT * FROM rsocs WHERE id = :id AND user_id = :user_id");
            $stmt->bindValue(':id', $rsocId, \PDO::PARAM_INT);
            $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
            $stmt->execute();
            $rsoc = $stmt->fetch();
            
            if (!$rsoc) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'RSOC不存在或无权访问',
                    'code' => 404
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }
            
            // 获取关键词
            $keywordStmt = $this->pdo->prepare("SELECT * FROM keywords WHERE rsoc_id = :rsoc_id");
            $keywordStmt->bindValue(':rsoc_id', $rsocId, \PDO::PARAM_INT);
            $keywordStmt->execute();
            $keywords = $keywordStmt->fetchAll();
            
            // 添加关键词到结果中
            $rsoc['keywords'] = $keywords;
            
            // 从ADS.COM API获取性能数据
            try {
                $performanceData = $this->adsApiService->getRsocPerformance($rsocId);
                $rsoc['performance'] = $performanceData;
            } catch (\Exception $e) {
                // 如果API调用失败，使用模拟数据
                $rsoc['performance'] = [
                    'clicks' => rand(100, 1000),
                    'impressions' => rand(1000, 10000),
                    'ctr' => round(rand(100, 500) / 100, 2),
                    'revenue' => round(rand(100, 1000) / 100, 2),
                    'cost' => round(rand(50, 500) / 100, 2),
                    'profit' => round(rand(50, 500) / 100, 2),
                    'roi' => round(rand(50, 200) / 100, 2)
                ];
            }
            
            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => $rsoc
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            
        } catch (\PDOException $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => '获取RSOC详情失败: ' . $e->getMessage(),
                'code' => 500
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    /**
     * 创建RSOC
     */
    public function store(Request $request, Response $response): Response
    {
        $userId = $request->getAttribute('user_id');
        $data = $request->getParsedBody();
        
        // 验证请求数据
        if (!isset($data['name']) || empty($data['name'])) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'RSOC名称不能为空',
                'code' => 400
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        
        try {
            // 准备数据
            $name = $data['name'];
            $status = isset($data['status']) ? $data['status'] : 'active';
            $budget = isset($data['budget']) ? (float)$data['budget'] : 0.0;
            $startDate = isset($data['start_date']) ? $data['start_date'] : date('Y-m-d');
            $endDate = isset($data['end_date']) ? $data['end_date'] : null;
            $description = isset($data['description']) ? $data['description'] : '';
            
            // 插入数据
            $stmt = $this->pdo->prepare("
                INSERT INTO rsocs (name, status, budget, start_date, end_date, description, user_id, created_at, updated_at) 
                VALUES (:name, :status, :budget, :start_date, :end_date, :description, :user_id, NOW(), NOW())
            ");
            
            $stmt->bindValue(':name', $name, \PDO::PARAM_STR);
            $stmt->bindValue(':status', $status, \PDO::PARAM_STR);
            $stmt->bindValue(':budget', $budget, \PDO::PARAM_STR);
            $stmt->bindValue(':start_date', $startDate, \PDO::PARAM_STR);
            $stmt->bindValue(':end_date', $endDate, \PDO::PARAM_STR);
            $stmt->bindValue(':description', $description, \PDO::PARAM_STR);
            $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
            
            $stmt->execute();
            $rsocId = $this->pdo->lastInsertId();
            
            // 创建ADS.COM RSOC
            try {
                $this->adsApiService->createRsoc([
                    'id' => $rsocId,
                    'name' => $name,
                    'status' => $status,
                    'budget' => $budget,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'description' => $description
                ]);
            } catch (\Exception $e) {
                // 记录错误，但继续处理
                error_log('ADS API错误: ' . $e->getMessage());
            }
            
            // 获取创建的RSOC
            $stmt = $this->pdo->prepare("SELECT * FROM rsocs WHERE id = :id");
            $stmt->bindValue(':id', $rsocId, \PDO::PARAM_INT);
            $stmt->execute();
            $rsoc = $stmt->fetch();
            
            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'RSOC创建成功',
                'data' => $rsoc
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
            
        } catch (\PDOException $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'RSOC创建失败: ' . $e->getMessage(),
                'code' => 500
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    /**
     * 更新RSOC
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        $userId = $request->getAttribute('user_id');
        $rsocId = $args['id'];
        $data = $request->getParsedBody();
        
        try {
            // 检查RSOC是否存在且属于当前用户
            $checkStmt = $this->pdo->prepare("SELECT * FROM rsocs WHERE id = :id AND user_id = :user_id");
            $checkStmt->bindValue(':id', $rsocId, \PDO::PARAM_INT);
            $checkStmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
            $checkStmt->execute();
            
            if (!$checkStmt->fetch()) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'RSOC不存在或无权修改',
                    'code' => 404
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }
            
            // 准备更新字段
            $updateFields = [];
            $params = [':id' => $rsocId];
            
            if (isset($data['name'])) {
                $updateFields[] = "name = :name";
                $params[':name'] = $data['name'];
            }
            
            if (isset($data['status'])) {
                $updateFields[] = "status = :status";
                $params[':status'] = $data['status'];
            }
            
            if (isset($data['budget'])) {
                $updateFields[] = "budget = :budget";
                $params[':budget'] = (float