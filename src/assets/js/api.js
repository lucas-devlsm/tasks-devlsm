// Módulo API para comunicação com o backend
class API {
    constructor() {
        this.baseURL = '/api';
        this.token = localStorage.getItem('token');
    }

    // Configurar headers padrão
    getHeaders() {
        const headers = {
            'Content-Type': 'application/json',
        };

        if (this.token) {
            headers['Authorization'] = `Bearer ${this.token}`;
        }

        return headers;
    }

    // Fazer requisição HTTP
    async request(endpoint, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        const config = {
            headers: this.getHeaders(),
            ...options
        };

        try {
            const response = await fetch(url, config);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || `Erro ${response.status}: ${response.statusText}`);
            }

            return data;
        } catch (error) {
            if (error.name === 'TypeError' && error.message.includes('fetch')) {
                throw new Error('Erro de conexão. Verifique se o servidor está rodando.');
            }
            throw error;
        }
    }

    // Autenticação
    async login(username, password) {
        const data = await this.request('/auth/login', {
            method: 'POST',
            body: JSON.stringify({ username, password })
        });

        this.token = data.token;
        localStorage.setItem('token', data.token);
        localStorage.setItem('user', JSON.stringify(data.user));

        return data;
    }

    async register(username, email, password) {
        return await this.request('/auth/register', {
            method: 'POST',
            body: JSON.stringify({ username, email, password })
        });
    }

    logout() {
        this.token = null;
        localStorage.removeItem('token');
        localStorage.removeItem('user');
    }

    // Verificar se está autenticado
    isAuthenticated() {
        return !!this.token;
    }

    // Tarefas
    async getTasks() {
        return await this.request('/tasks');
    }

    async getTask(id) {
        return await this.request(`/tasks/${id}`);
    }

    async createTask(taskData) {
        return await this.request('/tasks', {
            method: 'POST',
            body: JSON.stringify(taskData)
        });
    }

    async updateTask(id, taskData) {
        return await this.request(`/tasks/${id}`, {
            method: 'PUT',
            body: JSON.stringify(taskData)
        });
    }

    async deleteTask(id) {
        return await this.request(`/tasks/${id}`, {
            method: 'DELETE'
        });
    }

    async updateTaskStatus(id, status) {
        return await this.request(`/tasks/${id}/status`, {
            method: 'PATCH',
            body: JSON.stringify({ status })
        });
    }
}

// Exportar instância única
export const api = new API(); 