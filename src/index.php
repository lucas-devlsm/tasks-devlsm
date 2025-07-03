<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciador de Tarefas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-tasks me-2"></i>
                Gerenciador de Tarefas
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"><i class="fas fa-bars"></i></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarContent">
                <div class="navbar-nav ms-auto" id="authButtons">
                    <button class="btn btn-outline-light me-2" data-bs-toggle="modal" data-bs-target="#loginModal">
                        <i class="fas fa-sign-in-alt me-1"></i>Login
                    </button>
                    <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#registerModal">
                        <i class="fas fa-user-plus me-1"></i>Cadastrar
                    </button>
                </div>
                <div class="navbar-nav ms-auto d-none" id="userInfo">
                    <span class="navbar-text me-3 text-white">
                        Olá, <span id="username"></span>
                    </span>
                    <button class="btn btn-outline-light" onclick="logout()">
                        <i class="fas fa-sign-out-alt me-1"></i>Sair
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Container Principal -->
    <div class="container mt-4">
        <!-- Dashboard -->
        <div class="row mb-4" id="dashboard">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Total</h6>
                                <h3 id="totalTasks">0</h3>
                            </div>
                            <i class="fas fa-tasks fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Pendentes</h6>
                                <h3 id="pendingTasks">0</h3>
                            </div>
                            <i class="fas fa-clock fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Em Andamento</h6>
                                <h3 id="inProgressTasks">0</h3>
                            </div>
                            <i class="fas fa-spinner fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Concluídas</h6>
                                <h3 id="completedTasks">0</h3>
                            </div>
                            <i class="fas fa-check-circle fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botão Adicionar Tarefa -->
        <div class="row mb-4">
            <div class="col-12">
                <button class="btn btn-primary" onclick="openTaskModal()">
                    <i class="fas fa-plus me-2"></i>Nova Tarefa
                </button>
            </div>
        </div>

        <!-- Tabela de Tarefas -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list me-2"></i>Minhas Tarefas
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="tasksTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Título</th>
                                        <th>Descrição</th>
                                        <th>Status</th>
                                        <th>Criada em</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody id="tasksTableBody">
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Faça login para ver suas tarefas
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Login -->
    <div class="modal fade" id="loginModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="loginForm">
                        <div class="mb-3">
                            <label for="loginUsername" class="form-label">Usuário</label>
                            <input type="text" class="form-control" id="loginUsername" required>
                        </div>
                        <div class="mb-3">
                            <label for="loginPassword" class="form-label">Senha</label>
                            <input type="password" class="form-control" id="loginPassword" required>
                        </div>
                        <div class="alert alert-danger d-none" id="loginError"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="login()">
                        <i class="fas fa-sign-in-alt me-1"></i>Entrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Cadastro -->
    <div class="modal fade" id="registerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-plus me-2"></i>Cadastrar
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="registerForm">
                        <div class="mb-3">
                            <label for="registerUsername" class="form-label">Usuário</label>
                            <input type="text" class="form-control" id="registerUsername" required>
                        </div>
                        <div class="mb-3">
                            <label for="registerEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="registerEmail" required>
                        </div>
                        <div class="mb-3">
                            <label for="registerPassword" class="form-label">Senha</label>
                            <input type="password" class="form-control" id="registerPassword" required>
                        </div>
                        <div class="alert alert-danger d-none" id="registerError"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" onclick="register()">
                        <i class="fas fa-user-plus me-1"></i>Cadastrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Tarefa -->
    <div class="modal fade" id="taskModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="taskModalTitle">
                        <i class="fas fa-plus me-2"></i>Nova Tarefa
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="taskForm">
                        <input type="hidden" id="taskId">
                        <div class="mb-3">
                            <label for="taskTitle" class="form-label">Título *</label>
                            <input type="text" class="form-control" id="taskTitle" required maxlength="255">
                        </div>
                        <div class="mb-3">
                            <label for="taskDescription" class="form-label">Descrição</label>
                            <textarea class="form-control" id="taskDescription" rows="4" maxlength="1000"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="taskStatus" class="form-label">Status</label>
                            <select class="form-select" id="taskStatus">
                                <option value="pendente">Pendente</option>
                                <option value="em_andamento">Em Andamento</option>
                                <option value="concluida">Concluída</option>
                            </select>
                        </div>
                        <div class="alert alert-danger d-none" id="taskError"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="saveTask()">
                        <i class="fas fa-save me-1"></i>Salvar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="notificationToast" class="toast" role="alert">
            <div class="toast-header">
                <i class="fas fa-info-circle me-2"></i>
                <strong class="me-auto" id="toastTitle">Notificação</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body" id="toastMessage">
                Mensagem da notificação
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script type="module" src="assets/js/app.js"></script>
</body>
</html> 