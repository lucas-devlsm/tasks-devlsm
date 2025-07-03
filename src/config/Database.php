<?php

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $host = $_ENV['DB_HOST'] ?? 'db';
        $dbname = $_ENV['DB_NAME'] ?? 'task_manager';
        $username = $_ENV['DB_USER'] ?? 'task_user';
        $password = $_ENV['DB_PASS'] ?? 'task_password';

        try {
            $this->connection = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            throw new Exception("Erro de conexão com o banco de dados: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    // Prevenir clonagem
    private function __clone() {}

    // Prevenir desserialização
    public function __wakeup() {
        throw new Exception("Não é possível desserializar um singleton");
    }
} 