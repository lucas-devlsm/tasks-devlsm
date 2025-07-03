<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Tratar requisições OPTIONS (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Carregar dependências
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../utils/JWT.php';

// Função para enviar resposta JSON
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

// Função para enviar erro
function sendError($message, $statusCode = 400) {
    sendResponse(['error' => $message], $statusCode);
}

// Função para obter dados do corpo da requisição
function getRequestBody() {
    $input = file_get_contents('php://input');
    return json_decode($input, true) ?? [];
}

// Função para sanitizar dados
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Middleware de autenticação
function requireAuth() {
    $token = JWT::getTokenFromHeader();
    if (!$token) {
        sendError('Token de autenticação não fornecido', 401);
    }
    
    $payload = JWT::verify($token);
    if (!$payload) {
        sendError('Token inválido ou expirado', 401);
    }
    
    return $payload['user_id'];
}

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $path = str_replace('/api/', '', $path);
    $segments = explode('/', $path);
    
    $userModel = new User();
    $taskModel = new Task();
    
    // Rotas de autenticação
    if ($segments[0] === 'auth') {
        if ($method === 'POST' && $segments[1] === 'login') {
            $data = sanitizeInput(getRequestBody());
            
            if (empty($data['username']) || empty($data['password'])) {
                sendError('Usuário e senha são obrigatórios');
            }
            
            $user = $userModel->authenticate($data['username'], $data['password']);
            if (!$user) {
                sendError('Credenciais inválidas', 401);
            }
            
            $token = JWT::generate([
                'user_id' => $user['id'],
                'username' => $user['username'],
                'exp' => time() + (24 * 60 * 60) // 24 horas
            ]);
            
            sendResponse([
                'token' => $token,
                'user' => $user
            ]);
        }
        
        if ($method === 'POST' && $segments[1] === 'register') {
            $data = sanitizeInput(getRequestBody());
            
            $userId = $userModel->create(
                $data['username'] ?? '',
                $data['password'] ?? '',
                $data['email'] ?? ''
            );
            
            sendResponse(['message' => 'Usuário criado com sucesso', 'user_id' => $userId], 201);
        }
        
        sendError('Rota não encontrada', 404);
    }
    
    // Rotas de tarefas (requerem autenticação)
    if ($segments[0] === 'tasks') {
        $userId = requireAuth();
        
        if ($method === 'GET') {
            if (isset($segments[1])) {
                // GET /tasks/:id
                $task = $taskModel->getById($segments[1], $userId);
                if (!$task) {
                    sendError('Tarefa não encontrada', 404);
                }
                sendResponse($task);
            } else {
                // GET /tasks
                $tasks = $taskModel->getAll($userId);
                $stats = $taskModel->getStats($userId);
                sendResponse([
                    'tasks' => $tasks,
                    'stats' => $stats
                ]);
            }
        }
        
        if ($method === 'POST') {
            // POST /tasks
            $data = sanitizeInput(getRequestBody());
            $task = $taskModel->create($data, $userId);
            sendResponse($task, 201);
        }
        
        if ($method === 'PUT' && isset($segments[1])) {
            // PUT /tasks/:id
            $data = sanitizeInput(getRequestBody());
            $task = $taskModel->update($segments[1], $data, $userId);
            sendResponse($task);
        }
        
        if ($method === 'DELETE' && isset($segments[1])) {
            // DELETE /tasks/:id
            $taskModel->delete($segments[1], $userId);
            sendResponse(['message' => 'Tarefa excluída com sucesso']);
        }
        
        if ($method === 'PATCH' && isset($segments[1]) && isset($segments[2])) {
            // PATCH /tasks/:id/status
            if ($segments[2] === 'status') {
                $data = sanitizeInput(getRequestBody());
                if (!isset($data['status'])) {
                    sendError('Status é obrigatório');
                }
                $task = $taskModel->updateStatus($segments[1], $data['status'], $userId);
                sendResponse($task);
            }
        }
        
        sendError('Rota não encontrada', 404);
    }
    
    // Rota padrão
    sendError('Rota não encontrada', 404);
    
} catch (Exception $e) {
    sendError($e->getMessage(), 500);
} 