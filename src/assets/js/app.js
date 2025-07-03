// Aplicação principal do Gerenciador de Tarefas
import { api } from './api.js';
import { ui } from './ui.js';

class TaskManager {
    constructor() {
        this.tasks = [];
        this.currentUser = null;
        this.initialize();
    }

    // Inicializar aplicação
    async initialize() {
        this.checkAuth();
        this.setupGlobalFunctions();
        
        if (api.isAuthenticated()) {
            await this.loadTasks();
        }
    }

    // Verificar autenticação
    checkAuth() {
        const user = localStorage.getItem('user');
        if (user && api.isAuthenticated()) {
            this.currentUser = JSON.parse(user);
            ui.updateAuthUI(true, this.currentUser);
        } else {
            ui.updateAuthUI(false);
        }
    }

    // Configurar funções globais
    setupGlobalFunctions() {
        // Autenticação
        window.login = () => this.login();
        window.register = () => this.register();
        window.logout = () => this.logout();
        
        // Tarefas
        window.openTaskModal = () => this.openTaskModal();
        window.saveTask = () => this.saveTask();
        window.editTask = (id) => this.editTask(id);
        window.deleteTask = (id) => this.deleteTask(id);
        window.updateTaskStatus = (id, status) => this.updateTaskStatus(id, status);
    }

    // Login
    async login() {
        const username = ui.elements.loginUsername.value.trim();
        const password = ui.elements.loginPassword.value;

        if (!username || !password) {
            ui.showError('loginError', 'Usuário e senha são obrigatórios');
            return;
        }

        try {
            ui.setLoading(document.getElementById('loginSubmitBtn'), true);
            ui.hideError('loginError');

            const response = await api.login(username, password);
            this.currentUser = response.user;
            
            ui.updateAuthUI(true, this.currentUser);
            ui.showNotification('Sucesso', 'Login realizado com sucesso!', 'success');
            
            // Fechar modal e carregar tarefas
            const modal = bootstrap.Modal.getInstance(ui.elements.loginModal);
            modal.hide();
            
            await this.loadTasks();
            
        } catch (error) {
            ui.showError('loginError', error.message);
        } finally {
            ui.setLoading(document.getElementById('loginSubmitBtn'), false);
        }
    }

    // Registro
    async register() {
        const username = ui.elements.registerUsername.value.trim();
        const email = ui.elements.registerEmail.value.trim();
        const password = ui.elements.registerPassword.value;

        if (!username || !email || !password) {
            ui.showError('registerError', 'Todos os campos são obrigatórios');
            return;
        }

        if (password.length < 6) {
            ui.showError('registerError', 'A senha deve ter pelo menos 6 caracteres');
            return;
        }

        try {
            ui.setLoading(document.getElementById('registerSubmitBtn'), true);
            ui.hideError('registerError');

            await api.register(username, email, password);
            
            ui.showNotification('Sucesso', 'Usuário criado com sucesso! Faça login para continuar.', 'success');
            
            // Fechar modal e limpar formulário
            const modal = bootstrap.Modal.getInstance(ui.elements.registerModal);
            modal.hide();
            
            ui.elements.registerForm.reset();
            
        } catch (error) {
            ui.showError('registerError', error.message);
        } finally {
            ui.setLoading(document.getElementById('registerSubmitBtn'), false);
        }
    }

    // Logout
    logout() {
        api.logout();
        this.currentUser = null;
        this.tasks = [];
        
        ui.updateAuthUI(false);
        ui.updateDashboard({ total: 0, pendentes: 0, em_andamento: 0, concluidas: 0 });
        ui.renderTasksTable([]);
        
        ui.showNotification('Logout', 'Você foi desconectado com sucesso.', 'info');
    }

    // Carregar tarefas
    async loadTasks() {
        try {
            const response = await api.getTasks();
            this.tasks = response.tasks || [];
            
            ui.updateDashboard(response.stats || {});
            ui.renderTasksTable(this.tasks);
            
        } catch (error) {
            ui.showNotification('Erro', 'Erro ao carregar tarefas: ' + error.message, 'error');
        }
    }

    // Abrir modal de tarefa
    openTaskModal(task = null) {
        if (!api.isAuthenticated()) {
            // Abrir apenas o modal de login, não o de tarefa
            const loginModal = new bootstrap.Modal(ui.elements.loginModal);
            loginModal.show();
            return;
        }
        // Só abre o modal de tarefa se estiver autenticado
        ui.openTaskModal(task);
    }

    // Salvar tarefa
    async saveTask() {
        if (!ui.validateTaskForm()) {
            return;
        }

        const taskData = ui.getTaskFormData();
        const taskId = ui.elements.taskId.value;

        try {
            ui.setLoading(ui.elements.taskModal.querySelector('button[onclick="saveTask()"]'), true);
            ui.hideError('taskError');

            if (taskId) {
                // Atualizar tarefa existente
                await api.updateTask(taskId, taskData);
                ui.showNotification('Sucesso', 'Tarefa atualizada com sucesso!', 'success');
            } else {
                // Criar nova tarefa
                await api.createTask(taskData);
                ui.showNotification('Sucesso', 'Tarefa criada com sucesso!', 'success');
            }

            // Fechar modal e recarregar tarefas
            const modal = bootstrap.Modal.getInstance(ui.elements.taskModal);
            modal.hide();
            
            await this.loadTasks();
            
        } catch (error) {
            ui.showError('taskError', error.message);
        } finally {
            ui.setLoading(ui.elements.taskModal.querySelector('button[onclick="saveTask()"]'), false);
        }
    }

    // Editar tarefa
    async editTask(id) {
        try {
            const task = await api.getTask(id);
            ui.openTaskModal(task);
        } catch (error) {
            ui.showNotification('Erro', 'Erro ao carregar tarefa: ' + error.message, 'error');
        }
    }

    // Excluir tarefa
    async deleteTask(id) {
        if (!confirm('Tem certeza que deseja excluir esta tarefa?')) {
            return;
        }

        try {
            await api.deleteTask(id);
            ui.showNotification('Sucesso', 'Tarefa excluída com sucesso!', 'success');
            await this.loadTasks();
        } catch (error) {
            ui.showNotification('Erro', 'Erro ao excluir tarefa: ' + error.message, 'error');
        }
    }

    // Atualizar status da tarefa
    async updateTaskStatus(id, status) {
        try {
            await api.updateTaskStatus(id, status);
            ui.showNotification('Sucesso', 'Status da tarefa atualizado!', 'success');
            await this.loadTasks();
        } catch (error) {
            ui.showNotification('Erro', 'Erro ao atualizar status: ' + error.message, 'error');
        }
    }
}

// Inicializar aplicação quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', () => {
    new TaskManager();
}); 