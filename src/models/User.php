<?php

require_once __DIR__ . '/../config/Database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function authenticate($username, $password) {
        $stmt = $this->db->prepare("SELECT id, username, password, email FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            unset($user['password']); // Não retornar a senha
            return $user;
        }

        return false;
    }

    public function create($username, $password, $email) {
        // Validar dados
        if (empty($username) || empty($password) || empty($email)) {
            throw new Exception("Todos os campos são obrigatórios");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email inválido");
        }

        if (strlen($password) < 6) {
            throw new Exception("A senha deve ter pelo menos 6 caracteres");
        }

        // Verificar se usuário já existe
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->fetch()) {
            throw new Exception("Usuário ou email já existe");
        }

        // Criar usuário
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
        
        if ($stmt->execute([$username, $hashedPassword, $email])) {
            return $this->db->lastInsertId();
        }

        throw new Exception("Erro ao criar usuário");
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT id, username, email, created_at FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function update($id, $data) {
        $allowedFields = ['username', 'email'];
        $updates = [];
        $values = [];

        foreach ($data as $field => $value) {
            if (in_array($field, $allowedFields) && !empty($value)) {
                $updates[] = "$field = ?";
                $values[] = $value;
            }
        }

        if (empty($updates)) {
            throw new Exception("Nenhum campo válido para atualizar");
        }

        $values[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($values);
    }

    public function changePassword($id, $currentPassword, $newPassword) {
        // Verificar senha atual
        $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($currentPassword, $user['password'])) {
            throw new Exception("Senha atual incorreta");
        }

        if (strlen($newPassword) < 6) {
            throw new Exception("A nova senha deve ter pelo menos 6 caracteres");
        }

        // Atualizar senha
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
        
        return $stmt->execute([$hashedPassword, $id]);
    }
} 