/**
 * User Edit JavaScript
 * Funcionalidades de edição de usuários no painel admin
 */

class UserEdit {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
        this.initializeModal();
    }

    /**
     * Bind eventos dos elementos
     */
    bindEvents() {
        // Evento para abrir modal de edição
        document.addEventListener('click', (e) => {
            if (e.target.closest('.btn-edit-user')) {
                e.preventDefault();
                const button = e.target.closest('.btn-edit-user');
                this.openEditModal(button);
            }
        });

        // Evento para submeter formulário de edição
        const editForm = document.getElementById('editUserForm');
        if (editForm) {
            editForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitEditForm();
            });
        }
    }

    /**
     * Inicializa o modal de edição
     */
    initializeModal() {
        const modal = document.getElementById('editUserModal');
        if (modal) {
            this.editModal = new bootstrap.Modal(modal);
        }
    }

    /**
     * Abre o modal de edição com dados do usuário
     */
    openEditModal(button) {
        const userId = button.dataset.userId;
        const username = button.dataset.username;
        const email = button.dataset.email;

        // Preencher campos do modal
        document.getElementById('editUserId').value = userId;
        document.getElementById('editUsername').value = username;
        document.getElementById('editEmail').value = email;
        document.getElementById('editModalTitle').textContent = `Editar Usuário: ${username}`;

        // Mostrar modal
        this.editModal.show();
    }

    /**
     * Submete o formulário de edição
     */
    async submitEditForm() {
        const form = document.getElementById('editUserForm');
        const formData = new FormData(form);
        formData.append('action', 'edit'); // <- Correção essencial!
        const submitButton = form.querySelector('button[type="submit"]');

        this.showLoading(submitButton);

        try {
            const response = await fetch(window.location.href, {
                method: 'POST',
                body: formData
            });

            const responseText = await response.text();

            if (response.ok) {
                // Fechar modal
                this.editModal.hide();
                
                // Mostrar popup de sucesso
                this.showSuccess(responseText || 'Usuário atualizado com sucesso!');
                
                // Recarregar página após 1.5 segundos
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                this.showError(responseText || 'Erro ao atualizar usuário');
            }
        } catch (error) {
            this.showError('Erro de conexão');
        } finally {
            this.hideLoading(submitButton);
        }
    }

    /**
     * Mostra loading no botão
     */
    showLoading(button) {
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Salvando...';
    }

    /**
     * Remove loading do botão
     */
    hideLoading(button) {
        button.disabled = false;
        button.innerHTML = '<i class="fas fa-save me-2"></i>Salvar Alterações';
    }

    /**
     * Mostra erro como toast
     */
    showError(message) {
        const toastContainer = document.querySelector('.toast-container') || this.createToastContainer();

        const toastId = 'toast-error-' + Date.now();
        const toastHtml = `
            <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header bg-danger text-white">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong class="me-auto">Erro</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        `;

        toastContainer.insertAdjacentHTML('beforeend', toastHtml);

        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement);
        toast.show();

        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
    }

    /**
     * Mostra sucesso como toast
     */
    showSuccess(message) {
        const toastContainer = document.querySelector('.toast-container') || this.createToastContainer();

        const toastId = 'toast-success-' + Date.now();
        const toastHtml = `
            <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header bg-success text-white">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong class="me-auto">Sucesso</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        `;

        toastContainer.insertAdjacentHTML('beforeend', toastHtml);

        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
        toast.show();

        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
    }

    /**
     * Cria container para toasts se necessário
     */
    createToastContainer() {
        const container = document.createElement('div');
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(container);
        return container;
    }

    /**
     * Valida formulário de edição
     */
    validateEditForm() {
        const username = document.getElementById('editUsername').value.trim();
        const email = document.getElementById('editEmail').value.trim();

        if (!username) {
            this.showError('Nome de usuário é obrigatório');
            return false;
        }

        if (!email) {
            this.showError('Email é obrigatório');
            return false;
        }

        if (!this.isValidEmail(email)) {
            this.showError('Email inválido');
            return false;
        }

        return true;
    }

    /**
     * Valida formato de email
     */
    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
}

/**
 * Função global para abrir modal manualmente
 */
window.editUser = function(userId, username, email) {
    const button = document.createElement('button');
    button.className = 'btn-edit-user';
    button.dataset.userId = userId;
    button.dataset.username = username;
    button.dataset.email = email;

    if (window.userEdit) {
        window.userEdit.openEditModal(button);
    }
};

/**
 * Inicialização quando DOM estiver pronto
 */
document.addEventListener('DOMContentLoaded', function () {
    window.userEdit = new UserEdit();
});