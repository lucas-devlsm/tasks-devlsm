// Módulo UI para gerenciar a interface do usuário
class UI {
    constructor() {
        this.initializeElements();
        this.bindEvents();
    }

    // Inicializar elementos do DOM
    initializeElements() {
        this.elements = {
            // Autenticação
            authButtons: document.getElementById('authButtons'),
            userInfo: document.getElementById('userInfo'),
            username: document.getElementById('username'),
            
            // Dashboard
            totalTasks: document.getElementById('totalTasks'),
            pendingTasks: document.getElementById('pendingTasks'),
            inProgressTasks: document.getElementById('inProgressTasks'),
            completedTasks: document.getElementById('completedTasks'),
            
            // Tabela
            tasksTableBody: document.getElementById('tasksTableBody'),
            
            // Modais
            loginModal: document.getElementById('loginModal'),
            registerModal: document.getElementById('registerModal'),
            taskModal: document.getElementById('taskModal'),
            
            // Formulários
            loginForm: document.getElementById('loginForm'),
            registerForm: document.getElementById('registerForm'),
            taskForm: document.getElementById('taskForm'),
            
            // Campos de login
            loginUsername: document.getElementById('loginUsername'),
            loginPassword: document.getElementById('loginPassword'),
            loginError: document.getElementById('loginError'),
            
            // Campos de registro
            registerUsername: document.getElementById('registerUsername'),
            registerEmail: document.getElementById('registerEmail'),
            registerPassword: document.getElementById('registerPassword'),
            registerError: document.getElementById('registerError'),
            
            // Campos de tarefa
            taskId: document.getElementById('taskId'),
            taskTitle: document.getElementById('taskTitle'),
            taskDescription: document.getElementById('taskDescription'),
            taskStatus: document.getElementById('taskStatus'),
            taskError: document.getElementById('taskError'),
            taskModalTitle: document.getElementById('taskModalTitle'),
            
            // Toast
            notificationToast: document.getElementById('notificationToast'),
            toastTitle: document.getElementById('toastTitle'),
            toastMessage: document.getElementById('toastMessage')
        };
    }

    // Vincular eventos
    bindEvents() {
        // Eventos de formulário
        this.elements.loginForm.addEventListener('submit', (e) => e.preventDefault());
        this.elements.registerForm.addEventListener('submit', (e) => e.preventDefault());
        this.elements.taskForm.addEventListener('submit', (e) => e.preventDefault());
        
        // Eventos de tecla
        this.elements.loginPassword.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') window.login();
        });
        
        this.elements.registerPassword.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') window.register();
        });
        
        this.elements.taskTitle.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') window.saveTask();
        });
    }

    // Atualizar interface de autenticação
    updateAuthUI(isAuthenticated, user = null) {
        if (isAuthenticated && user) {
            this.elements.authButtons.classList.add('d-none');
            this.elements.userInfo.classList.remove('d-none');
            this.elements.username.textContent = user.username;
        } else {
            this.elements.authButtons.classList.remove('d-none');
            this.elements.userInfo.classList.add('d-none');
        }
    }

    // Atualizar dashboard
    updateDashboard(stats) {
        this.elements.totalTasks.textContent = stats.total || 0;
        this.elements.pendingTasks.textContent = stats.pendentes || 0;
        this.elements.inProgressTasks.textContent = stats.em_andamento || 0;
        this.elements.completedTasks.textContent = stats.concluidas || 0;
    }

    // Renderizar tabela de tarefas
    renderTasksTable(tasks) {
        if (!tasks || tasks.length === 0) {
            this.elements.tasksTableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-muted">
                        <i class="fas fa-inbox me-2"></i>
                        Nenhuma tarefa encontrada
                    </td>
                </tr>
            `;
            return;
        }

        this.elements.tasksTableBody.innerHTML = tasks.map(task => `
            <tr class="fade-in">
                <td>
                    <strong>${this.escapeHtml(task.title)}</strong>
                </td>
                <td>
                    <span class="text-muted">
                        ${this.escapeHtml(task.description || 'Sem descrição')}
                    </span>
                </td>
                <td>
                    <span class="status-badge status-${task.status}">
                        ${this.getStatusText(task.status)}
                    </span>
                </td>
                <td>
                    <small class="text-muted">
                        ${this.formatDate(task.created_at)}
                    </small>
                </td>
                <td>
                    <div class="btn-group btn-group-sm" role="group">
                        <button class="btn btn-outline-primary btn-action" 
                                onclick="window.editTask(${task.id})" 
                                title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        ${this.getStatusActionButton(task)}
                        <button class="btn btn-outline-danger btn-action" 
                                onclick="window.deleteTask(${task.id})" 
                                title="Excluir">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    // Obter botão de ação de status
    getStatusActionButton(task) {
        switch (task.status) {
            case 'pendente':
                return `
                    <button class="btn btn-outline-info btn-action" 
                            onclick="window.updateTaskStatus(${task.id}, 'em_andamento')" 
                            title="Iniciar">
                        <i class="fas fa-play"></i>
                    </button>
                `;
            case 'em_andamento':
                return `
                    <button class="btn btn-outline-success btn-action" 
                            onclick="window.updateTaskStatus(${task.id}, 'concluida')" 
                            title="Concluir">
                        <i class="fas fa-check"></i>
                    </button>
                `;
            case 'concluida':
                return `
                    <button class="btn btn-outline-warning btn-action" 
                            onclick="window.updateTaskStatus(${task.id}, 'pendente')" 
                            title="Reabrir">
                        <i class="fas fa-undo"></i>
                    </button>
                `;
            default:
                return '';
        }
    }

    // Abrir modal de tarefa
    openTaskModal(task = null) {
        // Impede abrir o modal de tarefa se não estiver autenticado
        if (typeof api !== 'undefined' && !api.isAuthenticated()) {
            // Fecha o modal de tarefa se estiver aberto
            if (this.elements.taskModal) {
                const modalInstance = bootstrap.Modal.getInstance(this.elements.taskModal);
                if (modalInstance) {
                    modalInstance.hide();
                }
            }
            return;
        }
        this.clearTaskForm();
        
        if (task) {
            this.elements.taskModalTitle.innerHTML = '<i class="fas fa-edit me-2"></i>Editar Tarefa';
            this.elements.taskId.value = task.id;
            this.elements.taskTitle.value = task.title;
            this.elements.taskDescription.value = task.description || '';
            this.elements.taskStatus.value = task.status;
        } else {
            this.elements.taskModalTitle.innerHTML = '<i class="fas fa-plus me-2"></i>Nova Tarefa';
        }

        const modal = new bootstrap.Modal(this.elements.taskModal);
        modal.show();
    }

    // Limpar formulário de tarefa
    clearTaskForm() {
        this.elements.taskId.value = '';
        this.elements.taskTitle.value = '';
        this.elements.taskDescription.value = '';
        this.elements.taskStatus.value = 'pendente';
        this.hideError('taskError');
    }

    // Obter dados do formulário de tarefa
    getTaskFormData() {
        return {
            title: this.elements.taskTitle.value.trim(),
            description: this.elements.taskDescription.value.trim(),
            status: this.elements.taskStatus.value
        };
    }

    // Validar formulário de tarefa
    validateTaskForm() {
        const data = this.getTaskFormData();
        
        if (!data.title) {
            this.showError('taskError', 'Título é obrigatório');
            return false;
        }
        
        if (data.title.length < 3) {
            this.showError('taskError', 'Título deve ter pelo menos 3 caracteres');
            return false;
        }
        
        if (data.description.length > 1000) {
            this.showError('taskError', 'Descrição deve ter no máximo 1000 caracteres');
            return false;
        }
        
        return true;
    }

    // Mostrar erro
    showError(elementId, message) {
        const element = this.elements[elementId];
        element.textContent = message;
        element.classList.remove('d-none');
    }

    // Ocultar erro
    hideError(elementId) {
        const element = this.elements[elementId];
        element.classList.add('d-none');
    }

    // Mostrar notificação toast
    showNotification(title, message, type = 'info') {
        this.elements.toastTitle.textContent = title;
        this.elements.toastMessage.textContent = message;
        
        // Configurar ícone baseado no tipo
        const iconMap = {
            success: 'fas fa-check-circle text-success',
            error: 'fas fa-exclamation-circle text-danger',
            warning: 'fas fa-exclamation-triangle text-warning',
            info: 'fas fa-info-circle text-info'
        };
        
        const iconElement = this.elements.notificationToast.querySelector('.toast-header i');
        iconElement.className = iconMap[type] || iconMap.info;
        
        const toast = new bootstrap.Toast(this.elements.notificationToast);
        toast.show();
    }

    // Utilitários
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    getStatusText(status) {
        const statusMap = {
            'pendente': 'Pendente',
            'em_andamento': 'Em Andamento',
            'concluida': 'Concluída'
        };
        return statusMap[status] || status;
    }

    // Loading states
    setLoading(element, isLoading) {
        if (!element) return;
        if (isLoading) {
            element.classList.add('loading');
            element.disabled = true;
        } else {
            element.classList.remove('loading');
            element.disabled = false;
        }
    }
}

// Exportar instância única
export const ui = new UI(); 