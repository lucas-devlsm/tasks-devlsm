/**
 * Admin Login JavaScript
 * Funcionalidades da tela de login do painel admin
 */

document.addEventListener('DOMContentLoaded', function() {
    // Auto-focus no campo usuário
    const usernameField = document.getElementById('username');
    if (usernameField) {
        usernameField.focus();
    }
    
    // Remover alertas após 5 segundos
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            if (bootstrap.Alert.getOrCreateInstance) {
                bootstrap.Alert.getOrCreateInstance(alert).close();
            }
        });
    }, 5000);
});
