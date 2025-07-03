<?php
session_start();
require_once '../config/database.php';

// Processar logout do admin
if ($_GET['action'] ?? '' === 'logout') {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Processar login do admin
if ($_POST['action'] ?? '' === 'admin_login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    try {
        $database = Database::getInstance();
        $pdo = $database->getConnection();
        
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Verificar se é admin (ID 1 ou username 'admin')
            if ($user['id'] == 1 || strtolower($user['username']) === 'admin') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_admin'] = true;
                
                header("Location: index.php");
                exit;
            } else {
                $loginError = "Acesso negado. Apenas administradores podem acessar esta área.";
            }
        } else {
            $loginError = "Credenciais inválidas.";
        }
    } catch (Exception $e) {
        $loginError = "Erro no sistema: " . $e->getMessage();
    }
}

// Verificar autenticação
$isLoggedIn = isset($_SESSION['user_id']) && isset($_SESSION['is_admin']);
$isAdmin = $isLoggedIn && $_SESSION['is_admin'];

// Se não estiver logado, mostrar tela de login
if (!$isLoggedIn || !$isAdmin) {
    include 'login.php';
    exit;
}

// Buscar informações do usuário atual
try {
    $database = Database::getInstance();
    $pdo = $database->getConnection();
    
    $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $currentUser = ['username' => $_SESSION['username'], 'id' => $_SESSION['user_id']];
}

// Buscar todos os usuários
try {
    $database = Database::getInstance();
    $pdo = $database->getConnection();
    
    $stmt = $pdo->query("SELECT id, username, email, created_at FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Contar tarefas por usuário
    $stmt = $pdo->query("
        SELECT user_id, 
               COUNT(*) as total_tasks,
               SUM(CASE WHEN status = 'pendente' THEN 1 ELSE 0 END) as pending,
               SUM(CASE WHEN status = 'em_andamento' THEN 1 ELSE 0 END) as in_progress,
               SUM(CASE WHEN status = 'concluida' THEN 1 ELSE 0 END) as completed
        FROM tasks 
        GROUP BY user_id
    ");
    $taskStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Organizar stats por user_id
    $statsById = [];
    foreach ($taskStats as $stat) {
        $statsById[$stat['user_id']] = $stat;
    }
    
} catch (Exception $e) {
    $error = "Erro ao carregar usuários: " . $e->getMessage();
}

// Processar ações via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        // --- EXCLUIR USUÁRIO ---
        $userId = (int) ($_POST['user_id'] ?? 0);

        if ($userId > 0) {
            // Proteger admin principal (ID 1) da exclusão
            if ($userId === 1) {
                http_response_code(403);
                exit('Não é possível excluir o administrador principal');
            }
            
            try {
                // Excluir tarefas associadas primeiro
                $stmtTasks = $pdo->prepare("DELETE FROM tasks WHERE user_id = ?");
                $stmtTasks->execute([$userId]);

                // Excluir o usuário
                $stmtUser = $pdo->prepare("DELETE FROM users WHERE id = ?");
                if ($stmtUser->execute([$userId])) {
                    http_response_code(200);
                    exit('Usuário deletado com sucesso');
                } else {
                    http_response_code(500);
                    exit('Erro ao deletar usuário');
                }
            } catch (Exception $e) {
                http_response_code(500);
                exit('Erro interno');
            }
        } else {
            http_response_code(400);
            exit('ID de usuário inválido');
        }

    } elseif ($action === 'edit') {
        // --- EDITAR USUÁRIO ---
        $userId = (int) ($_POST['user_id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if ($userId > 0 && $username && $email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            try {
                // Verificar se username ou email já existem (exceto para o próprio usuário)
                $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
                $stmtCheck->execute([$username, $email, $userId]);
                
                if ($stmtCheck->fetch()) {
                    http_response_code(409);
                    exit('Nome de usuário ou email já existem');
                }

                // Atualizar usuário
                $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                if ($stmt->execute([$username, $email, $userId])) {
                    http_response_code(200);
                    exit('Usuário atualizado com sucesso');
                } else {
                    http_response_code(500);
                    exit('Erro ao atualizar usuário');
                }
            } catch (Exception $e) {
                http_response_code(500);
                exit('Erro interno');
            }
        } else {
            http_response_code(400);
            exit('Dados inválidos ou email mal formatado');
        }

    } else {
        http_response_code(400);
        exit('Ação inválida');
    }
} else if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit('Método não permitido');
}

// Calcular totais para o dashboard
$totalUsers = count($users);
$totalTasks = array_sum(array_column($taskStats, 'total_tasks'));
$totalPending = array_sum(array_column($taskStats, 'pending'));
$totalCompleted = array_sum(array_column($taskStats, 'completed'));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Usuários - Gerenciador de Tarefas</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Admin CSS -->
    <link href="assets/css/admin.css" rel="stylesheet">
    
    <!-- Meta tags para SEO -->
    <meta name="description" content="Painel de administração de usuários do Gerenciador de Tarefas">
    <meta name="author" content="Tecsa Group">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../assets/favicon.ico">
</head>
<body>
    <!-- Header Admin -->
    <header class="admin-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 col-md-6">
                    <h1>
                        <i class="fas fa-users-cog me-3"></i>
                        Painel de Usuários
                    </h1>
                    <p class="mb-0">Gerenciamento completo de usuários do sistema</p>
                </div>
                <div class="col-lg-6 col-md-6 text-end">
                    <div class="d-flex flex-column flex-md-row align-items-center justify-content-end gap-2">
                        <div class="text-white me-3">
                            <small>Logado como:</small><br>
                            <strong><?= htmlspecialchars($currentUser['username']) ?></strong>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="../index.php" class="btn btn-light">
                                <i class="fas fa-arrow-left me-2"></i>
                                Voltar ao App
                            </a>
                            <a href="index.php?action=logout" class="btn btn-outline-light">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                Sair
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="container">
        <!-- Alertas -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?= htmlspecialchars($_GET['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
        <?php endif; ?>

        <!-- Dashboard de Estatísticas -->
        <section class="row mb-4" aria-label="Estatísticas do Sistema">
            <div class="col-lg-3 col-md-6">
                <div class="card stats-card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Total de Usuários</h6>
                                <h3 class="mb-0"><?= $totalUsers ?></h3>
                            </div>
                            <i class="fas fa-users" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="card stats-card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Total de Tarefas</h6>
                                <h3 class="mb-0"><?= $totalTasks ?></h3>
                            </div>
                            <i class="fas fa-tasks" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="card stats-card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Tarefas Pendentes</h6>
                                <h3 class="mb-0"><?= $totalPending ?></h3>
                            </div>
                            <i class="fas fa-clock" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="card stats-card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Tarefas Concluídas</h6>
                                <h3 class="mb-0"><?= $totalCompleted ?></h3>
                            </div>
                            <i class="fas fa-check-circle" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Lista de Usuários -->
        <section class="card main-card">
            <header class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>
                    Lista de Usuários (<?= $totalUsers ?>)
                </h5>
            </header>
            
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" aria-label="Lista de usuários do sistema">
                        <thead>
                            <tr>
                                <th scope="col">Usuário</th>
                                <th scope="col">Email</th>
                                <th scope="col">Cadastrado em</th>
                                <th scope="col">Tarefas</th>
                                <th scope="col">Status</th>
                                <th scope="col">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="6" class="empty-state">
                                        <i class="fas fa-users" aria-hidden="true"></i>
                                        <h5>Nenhum usuário encontrado</h5>
                                        <p class="text-muted mb-0">Não há usuários cadastrados no sistema.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                    <?php $stats = $statsById[$user['id']] ?? null; ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="user-avatar me-3" title="<?= htmlspecialchars($user['username']) ?>">
                                                    <?= strtoupper(substr($user['username'], 0, 1)) ?>
                                                </div>
                                                <div class="user-info">
                                                    <strong><?= htmlspecialchars($user['username']) ?></strong>
                                                    <br>
                                                    <small class="text-muted">ID: <?= $user['id'] ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="mailto:<?= htmlspecialchars($user['email']) ?>" class="text-decoration-none">
                                                <?= htmlspecialchars($user['email']) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar-alt me-1"></i>
                                                <?= date('d/m/Y H:i', strtotime($user['created_at'])) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?php if ($stats): ?>
                                                <div class="d-flex flex-wrap gap-1">
                                                    <span class="badge bg-primary" title="Total de tarefas">
                                                        <?= $stats['total_tasks'] ?> Total
                                                    </span>
                                                    <span class="badge bg-warning" title="Tarefas pendentes">
                                                        <?= $stats['pending'] ?> Pendentes
                                                    </span>
                                                    <span class="badge bg-success" title="Tarefas concluídas">
                                                        <?= $stats['completed'] ?> Concluídas
                                                    </span>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">
                                                    <i class="fas fa-inbox me-1"></i>
                                                    Sem tarefas
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle me-1"></i>
                                                Ativo
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <button 
                                                    class="btn btn-outline-primary btn-action btn-edit-user" 
                                                    data-user-id="<?= $user['id'] ?>"
                                                    data-username="<?= htmlspecialchars($user['username'], ENT_QUOTES) ?>"
                                                    data-email="<?= htmlspecialchars($user['email'], ENT_QUOTES) ?>"
                                                    title="Editar usuário"
                                                    aria-label="Editar usuário <?= htmlspecialchars($user['username']) ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php if ($user['id'] != 1): ?>
                                                    <button 
                                                        class="btn btn-outline-danger btn-action" 
                                                        onclick="deleteUser(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username'], ENT_QUOTES) ?>')"
                                                        title="Deletar usuário"
                                                        aria-label="Deletar usuário <?= htmlspecialchars($user['username']) ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button 
                                                        class="btn btn-outline-secondary btn-action" 
                                                        disabled
                                                        title="Admin principal não pode ser excluído"
                                                        aria-label="Admin principal protegido">
                                                        <i class="fas fa-shield-alt"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <!-- Modal de Edição de Usuário -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editModalTitle">
                        <i class="fas fa-edit me-2"></i>
                        Editar Usuário
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <form id="editUserForm" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="user_id" id="editUserId">
                        
                        <div class="mb-3">
                            <label for="editUsername" class="form-label">
                                <i class="fas fa-user me-2"></i>Nome de Usuário
                            </label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="editUsername" 
                                name="username" 
                                required 
                                placeholder="Digite o nome de usuário">
                        </div>
                        
                        <div class="mb-3">
                            <label for="editEmail" class="form-label">
                                <i class="fas fa-envelope me-2"></i>Email
                            </label>
                            <input 
                                type="email" 
                                class="form-control" 
                                id="editEmail" 
                                name="email" 
                                required 
                                placeholder="Digite o email">
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Nota:</strong> A senha não será alterada. Para alterar a senha, o usuário deve fazer isso através da aplicação principal.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>
                            Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Confirmar Exclusão
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja deletar o usuário <strong id="deleteUsername"></strong>?</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Atenção:</strong> Todas as tarefas deste usuário também serão deletadas permanentemente.
                    </div>
                    <input type="hidden" id="deleteUserId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-danger" onclick="window.adminPanel.deleteUser(document.getElementById('deleteUserId').value)">
                        <i class="fas fa-trash me-1"></i>
                        Deletar Usuário
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin.js"></script>
    <script src="assets/js/user-edit.js"></script>
</body>
</html> 