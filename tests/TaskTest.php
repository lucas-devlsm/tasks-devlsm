<?php

require_once __DIR__ . '/../src/config/Database.php';
require_once __DIR__ . '/../src/models/Task.php';

class TaskTest extends PHPUnit\Framework\TestCase {
    private $task;
    private $db;

    protected function setUp(): void {
        // Configurar banco de dados de teste
        try {
            $this->db = Database::getInstance()->getConnection();
            $this->task = new Task();
            
            // Verificar se a conexão está funcionando
            $this->assertNotNull($this->db, 'Conexão com banco de dados não pode ser nula');
            
            // Limpar tabela de tarefas antes de cada teste
            $this->db->exec("DELETE FROM tasks");
            $this->db->exec("ALTER TABLE tasks AUTO_INCREMENT = 1");
            
        } catch (Exception $e) {
            $this->markTestSkipped('Banco de dados não disponível: ' . $e->getMessage());
        }
    }

    protected function tearDown(): void {
        // Limpar dados após cada teste
        if ($this->db) {
            try {
                $this->db->exec("DELETE FROM tasks");
                $this->db->exec("ALTER TABLE tasks AUTO_INCREMENT = 1");
            } catch (Exception $e) {
                // Ignorar erros de limpeza
            }
        }
    }

    public function testCreateTask() {
        $taskData = [
            'title' => 'Teste de Tarefa',
            'description' => 'Descrição de teste',
            'status' => 'pendente'
        ];

        $result = $this->task->create($taskData, 1);
        
        $this->assertIsArray($result);
        $this->assertEquals('Teste de Tarefa', $result['title']);
        $this->assertEquals('Descrição de teste', $result['description']);
        $this->assertEquals('pendente', $result['status']);
        $this->assertEquals(1, $result['user_id']);
    }

    public function testCreateTaskWithoutTitle() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Título é obrigatório');

        $taskData = [
            'description' => 'Descrição de teste'
        ];

        $this->task->create($taskData, 1);
    }

    public function testCreateTaskWithShortTitle() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Título deve ter entre 3 e 255 caracteres');

        $taskData = [
            'title' => 'ab'
        ];

        $this->task->create($taskData, 1);
    }

    public function testCreateTaskWithLongDescription() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Descrição deve ter no máximo 1000 caracteres');

        $taskData = [
            'title' => 'Tarefa Teste',
            'description' => str_repeat('a', 1001)
        ];

        $this->task->create($taskData, 1);
    }

    public function testGetAllTasks() {
        // Criar algumas tarefas
        $task1 = $this->task->create(['title' => 'Tarefa 1'], 1);
        $task2 = $this->task->create(['title' => 'Tarefa 2'], 1);
        $task3 = $this->task->create(['title' => 'Tarefa 3'], 2);

        // Buscar todas as tarefas
        $allTasks = $this->task->getAll();
        $this->assertCount(3, $allTasks);

        // Buscar tarefas do usuário 1
        $userTasks = $this->task->getAll(1);
        $this->assertCount(2, $userTasks);
        $this->assertEquals(1, $userTasks[0]['user_id']);
        $this->assertEquals(1, $userTasks[1]['user_id']);
    }

    public function testGetTaskById() {
        $createdTask = $this->task->create(['title' => 'Tarefa Teste'], 1);
        
        $foundTask = $this->task->getById($createdTask['id']);
        
        $this->assertIsArray($foundTask);
        $this->assertEquals($createdTask['id'], $foundTask['id']);
        $this->assertEquals('Tarefa Teste', $foundTask['title']);
    }

    public function testGetTaskByIdNotFound() {
        $task = $this->task->getById(999);
        $this->assertFalse($task);
    }

    public function testUpdateTask() {
        $createdTask = $this->task->create(['title' => 'Tarefa Original'], 1);
        
        $updateData = [
            'title' => 'Tarefa Atualizada',
            'description' => 'Nova descrição',
            'status' => 'em_andamento'
        ];

        $updatedTask = $this->task->update($createdTask['id'], $updateData, 1);
        
        $this->assertEquals('Tarefa Atualizada', $updatedTask['title']);
        $this->assertEquals('Nova descrição', $updatedTask['description']);
        $this->assertEquals('em_andamento', $updatedTask['status']);
    }

    public function testUpdateTaskNotFound() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Tarefa não encontrada');

        $this->task->update(999, ['title' => 'Teste'], 1);
    }

    public function testUpdateTaskWrongUser() {
        $createdTask = $this->task->create(['title' => 'Tarefa Teste'], 1);
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Tarefa não encontrada');

        $this->task->update($createdTask['id'], ['title' => 'Teste'], 2);
    }

    public function testDeleteTask() {
        $createdTask = $this->task->create(['title' => 'Tarefa para Excluir'], 1);
        
        $result = $this->task->delete($createdTask['id'], 1);
        $this->assertTrue($result);
        
        // Verificar se foi realmente excluída
        $deletedTask = $this->task->getById($createdTask['id']);
        $this->assertFalse($deletedTask);
    }

    public function testDeleteTaskNotFound() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Tarefa não encontrada');

        $this->task->delete(999, 1);
    }

    public function testUpdateTaskStatus() {
        $createdTask = $this->task->create(['title' => 'Tarefa Teste'], 1);
        
        $updatedTask = $this->task->updateStatus($createdTask['id'], 'concluida', 1);
        $this->assertEquals('concluida', $updatedTask['status']);
    }

    public function testUpdateTaskStatusInvalid() {
        $createdTask = $this->task->create(['title' => 'Tarefa Teste'], 1);
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Status inválido');

        $this->task->updateStatus($createdTask['id'], 'status_invalido', 1);
    }

    public function testGetStats() {
        // Criar tarefas com diferentes status
        $this->task->create(['title' => 'Tarefa 1', 'status' => 'pendente'], 1);
        $this->task->create(['title' => 'Tarefa 2', 'status' => 'em_andamento'], 1);
        $this->task->create(['title' => 'Tarefa 3', 'status' => 'concluida'], 1);
        $this->task->create(['title' => 'Tarefa 4', 'status' => 'pendente'], 2);

        $stats = $this->task->getStats();
        $this->assertEquals(4, $stats['total']);
        $this->assertEquals(2, $stats['pendentes']);
        $this->assertEquals(1, $stats['em_andamento']);
        $this->assertEquals(1, $stats['concluidas']);

        // Stats do usuário 1
        $userStats = $this->task->getStats(1);
        $this->assertEquals(3, $userStats['total']);
        $this->assertEquals(1, $userStats['pendentes']);
        $this->assertEquals(1, $userStats['em_andamento']);
        $this->assertEquals(1, $userStats['concluidas']);
    }

    /**
     * Teste para verificar se o banco de dados está acessível
     */
    public function testDatabaseConnection() {
        $this->assertNotNull($this->db);
        $this->assertInstanceOf(PDO::class, $this->db);
        
        // Testar uma query simples
        $result = $this->db->query("SELECT 1 as test");
        $this->assertNotFalse($result);
        
        $row = $result->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals(1, $row['test']);
    }
} 