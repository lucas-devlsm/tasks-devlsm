/**
 * Admin Panel JavaScript
 * Gerenciador de Tarefas - Painel de Usuários
 */

class AdminPanel {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
        this.autoHideAlerts();
        this.addAnimations();
    }

    /**
     * Bind eventos dos elementos
     */
    bindEvents() {
        // Espaço reservado para eventos futuros
    }

    /**
     * Auto-hide alerts após 5 segundos
     */
    autoHideAlerts() {
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                if (bootstrap.Alert.getOrCreateInstance) {
                    bootstrap.Alert.getOrCreateInstance(alert).close();
                }
            });
        }, 5000);
    }

    /**
     * Adiciona animações aos elementos
     */
    addAnimations() {
        const statsCards = document.querySelectorAll('.stats-card');
        statsCards.forEach((card, index) => {
            setTimeout(() => {
                card.classList.add('fade-in-up');
            }, index * 100);
        });

        const tableRows = document.querySelectorAll('.table tbody tr');
        tableRows.forEach((row, index) => {
            setTimeout(() => {
                row.classList.add('fade-in-up');
            }, 300 + index * 50);
        });
    }

    /**
     * Mostra loading em um elemento
     */
    showLoading(element) {
        element.classList.add('loading');
    }

    /**
     * Remove loading de um elemento
     */
    hideLoading(element) {
        element.classList.remove('loading');
    }

    /**
     * Mostra notificação toast
     */
    showNotification(message, type = 'info') {
        const toastContainer = document.querySelector('.toast-container') || this.createToastContainer();
        
        const toastId = 'toast-' + Date.now();
        const headerClass = type === 'success' ? 'bg-success text-white' : 
                          type === 'error' ? 'bg-danger text-white' : 
                          type === 'warning' ? 'bg-warning text-dark' : 'bg-info text-white';
        
        const toastHtml = `
            <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header ${headerClass}">
                    <i class="fas fa-${this.getToastIcon(type)} me-2"></i>
                    <strong class="me-auto">Admin Panel</strong>
                    <button type="button" class="btn-close ${type === 'success' || type === 'error' ? 'btn-close-white' : ''}" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">${message}</div>
            </div>
        `;

        toastContainer.insertAdjacentHTML('beforeend', toastHtml);

        const toastElement = document.getElementById(toastId);
        const delay = type === 'success' ? 3000 : 5000; // Sucesso fica 3s, outros 5s
        const toast = new bootstrap.Toast(toastElement, { delay: delay });
        toast.show();

        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
    }

    /**
     * Cria container para toasts se não existir
     */
    createToastContainer() {
        const container = document.createElement('div');
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(container);
        return container;
    }

    /**
     * Retorna ícone baseado no tipo de toast
     */
    getToastIcon(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        return icons[type] || icons.info;
    }

    /**
     * Confirma e executa exclusão de usuário
     */
    confirmDeleteUser(userId, username) {
        if (confirm(`Tem certeza que deseja deletar o usuário "${username}"?\n\nTodas as tarefas deste usuário também serão deletadas permanentemente.`)) {
            this.deleteUser(userId);
        }
    }

    /**
     * Deleta usuário via AJAX
     */
    async deleteUser(userId) {
        try {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('user_id', userId);

            const response = await fetch(window.location.href, {
                method: 'POST',
                body: formData
            });

            const responseText = await response.text();

            if (response.ok) {
                // Fechar modal de confirmação
                const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
                if (deleteModal) {
                    deleteModal.hide();
                }
                
                // Mostrar popup de sucesso
                this.showNotification(responseText || 'Usuário deletado com sucesso!', 'success');
                
                // Recarregar página após 1.5 segundos
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                this.showNotification(responseText || 'Erro ao deletar usuário', 'error');
            }
        } catch (error) {
            this.showNotification('Erro de conexão', 'error');
        }
    }

    /**
     * Formata números para exibição
     */
    formatNumber(num) {
        return new Intl.NumberFormat('pt-BR').format(num);
    }

    /**
     * Atualiza estatísticas em tempo real (futuro)
     */
    updateStats() {
        // Implementar se necessário
    }
}

/**
 * Função global: abre modal de exclusão (se for usado com Bootstrap Modal)
 */
window.deleteUser = function(userId, username) {
    document.getElementById('deleteUserId').value = userId;
    document.getElementById('deleteUsername').textContent = username;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
};

/**
 * Inicialização quando DOM estiver pronto
 */
document.addEventListener('DOMContentLoaded', function () {
    window.adminPanel = new AdminPanel();

    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
