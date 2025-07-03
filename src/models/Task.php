<?php

require_once __DIR__ . '/../config/Database.php';

class Task {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($userId = null) {
        $sql = "SELECT t.*, u.username as user_name 
                FROM tasks t 
                LEFT JOIN users u ON t.user_id = u.id";
        
        $params = [];
        if ($userId) {
            $sql .= " WHERE t.user_id = ?";
            $params[] = $userId;
        }
        
        $sql .= " ORDER BY t.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById($id, $userId = null) {
        $sql = "SELECT t.*, u.username as user_name 
                FROM tasks t 
                LEFT JOIN users u ON t.user_id = u.id 
                WHERE t.id = ?";
        
        $params = [$id];
        if ($userId) {
            $sql .= " AND t.user_id = ?";
            $params[] = $userId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function create($data, $userId) {
        // Validar dados
        $this->validateTaskData($data);
        
        $stmt = $this->db->prepare("
            INSERT INTO tasks (title, description, status, user_id) 
            VALUES (?, ?, ?, ?)
        ");
        
        $status = $data['status'] ?? 'pendente';
        
        if ($stmt->execute([
            $data['title'],
            $data['description'] ?? '',
            $status,
            $userId
        ])) {
            return $this->getById($this->db->lastInsertId());
        }
        
        throw new Exception("Erro ao criar tarefa");
    }

    public function update($id, $data, $userId = null) {
        // Verificar se a tarefa existe e pertence ao usuário
        $task = $this->getById($id, $userId);
        if (!$task) {
            throw new Exception("Tarefa não encontrada");
        }
        
        // Validar dados
        $this->validateTaskData($data, true);
        
        $updates = [];
        $values = [];
        
        $allowedFields = ['title', 'description', 'status'];
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $values[] = $data[$field];
            }
        }
        
        if (empty($updates)) {
            throw new Exception("Nenhum campo válido para atualizar");
        }
        
        $values[] = $id;
        if ($userId) {
            $values[] = $userId;
        }
        
        $sql = "UPDATE tasks SET " . implode(', ', $updates) . " WHERE id = ?";
        if ($userId) {
            $sql .= " AND user_id = ?";
        }
        
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute($values)) {
            return $this->getById($id);
        }
        
        throw new Exception("Erro ao atualizar tarefa");
    }

    public function delete($id, $userId = null) {
        // Verificar se a tarefa existe e pertence ao usuário
        $task = $this->getById($id, $userId);
        if (!$task) {
            throw new Exception("Tarefa não encontrada");
        }
        
        $sql = "DELETE FROM tasks WHERE id = ?";
        $params = [$id];
        
        if ($userId) {
            $sql .= " AND user_id = ?";
            $params[] = $userId;
        }
        
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute($params)) {
            return true;
        }
        
        throw new Exception("Erro ao excluir tarefa");
    }

    public function updateStatus($id, $status, $userId = null) {
        $validStatuses = ['pendente', 'em_andamento', 'concluida'];
        
        if (!in_array($status, $validStatuses)) {
            throw new Exception("Status inválido");
        }
        
        return $this->update($id, ['status' => $status], $userId);
    }

    private function validateTaskData($data, $isUpdate = false) {
        if (!$isUpdate && empty($data['title'])) {
            throw new Exception("Título é obrigatório");
        }
        
        if (isset($data['title']) && (strlen($data['title']) < 3 || strlen($data['title']) > 255)) {
            throw new Exception("Título deve ter entre 3 e 255 caracteres");
        }
        
        if (isset($data['description']) && strlen($data['description']) > 1000) {
            throw new Exception("Descrição deve ter no máximo 1000 caracteres");
        }
        
        if (isset($data['status'])) {
            $validStatuses = ['pendente', 'em_andamento', 'concluida'];
            if (!in_array($data['status'], $validStatuses)) {
                throw new Exception("Status inválido");
            }
        }
    }

    public function getStats($userId = null) {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pendente' THEN 1 ELSE 0 END) as pendentes,
                    SUM(CASE WHEN status = 'em_andamento' THEN 1 ELSE 0 END) as em_andamento,
                    SUM(CASE WHEN status = 'concluida' THEN 1 ELSE 0 END) as concluidas
                FROM tasks";
        
        $params = [];
        if ($userId) {
            $sql .= " WHERE user_id = ?";
            $params[] = $userId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
} 