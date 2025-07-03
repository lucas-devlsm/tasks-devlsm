-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS task_manager;
USE task_manager;

-- Criar tabela de usuários para autenticação JWT
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Criar tabela de tarefas
CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('pendente', 'em_andamento', 'concluida') DEFAULT 'pendente',
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Inserir apenas o usuário admin (senha: admin123)
INSERT INTO users (username, password, email) VALUES 
('admin', '$2y$10$G/OIbjWnGN306ABLDWOOvO6dB7GoDsvA3oRMI3BuicrDbRj5FrIQu', 'admin@example.com'); 