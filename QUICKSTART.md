# 🚀 Início Rápido - Gerenciador de Tarefas

## ⚡ Execução em 3 Passos

### 1. Subir a aplicação
```bash
docker-compose up -d
```

### 2. Acessar a aplicação
- **URL**: http://localhost:9000
- **Usuário**: `admin`
- **Senha**: `admin123`

### 3. Começar a usar
- Faça login com as credenciais acima
- Clique em "Nova Tarefa" para criar sua primeira tarefa
- Use os botões de ação para gerenciar suas tarefas

## 🔧 Comandos Úteis

### Ver logs
```bash
docker-compose logs -f
```

### Parar aplicação
```bash
docker-compose down
```

### Executar testes
```bash
./run-tests.sh
```

### Acessar phpMyAdmin
- **URL**: http://localhost:8080
- **Usuário**: `task_user`
- **Senha**: `task_password`

### Acessar Painel de Administração
- **URL**: http://localhost:9000/admin/
- **Login**: Tela de login dedicada (não precisa estar logado previamente)
- **Credenciais**: `admin` / `admin123`
- **Funcionalidades**:
  - Tela de login exclusiva para administradores
  - Dashboard com estatísticas gerais
  - Lista completa de usuários
  - Estatísticas de tarefas por usuário
  - Edição de dados de usuários
  - Gerenciamento de usuários (deletar)
  - **Proteção do admin principal** (não pode ser excluído)
  - Interface responsiva e moderna
  - Sistema de logout independente
  - Feedback visual com popups de sucesso/erro

## 📱 Funcionalidades Principais

- ✅ **Dashboard** com estatísticas
- ✅ **CRUD completo** de tarefas
- ✅ **Autenticação JWT**
- ✅ **Interface responsiva**
- ✅ **Validação de dados**
- ✅ **Notificações toast**
- ✅ **Painel de administração**

## 🆘 Solução de Problemas

### Porta 9000 ocupada
```bash
# Parar containers
docker-compose down

# Verificar portas em uso
netstat -tulpn | grep 9000

# Alterar porta no docker-compose.yml se necessário
```

### Erro de conexão com banco
```bash
# Verificar se MySQL está rodando
docker-compose ps

# Reiniciar apenas o banco
docker-compose restart db
```

### Problemas de permissão
```bash
# Dar permissão ao script de testes
chmod +x run-tests.sh
```

### Acesso negado ao painel admin
- O painel admin agora tem tela de login própria
- Acesse http://localhost:9000/admin/ diretamente
- Use as credenciais: `admin` / `admin123`
- Apenas usuários com ID 1 ou username 'admin' podem acessar

Se encontrar problemas, verifique:
1. Docker está rodando
2. Portas 9000 e 8080 estão livres
3. Logs do container: `docker-compose logs web`

## 🔒 Versão Final

Esta é a versão de produção com:
- ✅ **Admin principal protegido** (ID 1 não pode ser excluído)
- ✅ **Interface adaptativa** (botão de proteção para admin)
- ✅ **Código limpo** (sem logs de debug)
- ✅ **Feedback visual** completo
- ✅ **Pronto para GitHub**

---

**🎉 Pronto! Sua aplicação está funcionando!** 