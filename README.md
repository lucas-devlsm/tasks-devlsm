# Gerenciador de Tarefas

Uma aplicação completa de gerenciamento de tarefas desenvolvida com PHP, MySQL, HTML, CSS, JavaScript e Docker.

## 🚀 Funcionalidades

### Backend (PHP)
- ✅ API RESTful completa
- ✅ Autenticação JWT
- ✅ Validação de dados
- ✅ Sanitização de entrada
- ✅ Padrão Singleton para conexão com banco
- ✅ Testes unitários com PHPUnit
- ✅ Proteção do administrador principal

### Frontend (JavaScript Vanilla)
- ✅ Interface responsiva com Bootstrap
- ✅ Módulos ES6 organizados
- ✅ Validação client-side
- ✅ Notificações toast
- ✅ Dashboard com estatísticas
- ✅ CRUD completo de tarefas

### Painel de Administração
- ✅ Sistema de login independente
- ✅ Gerenciamento completo de usuários
- ✅ Edição de dados de usuários
- ✅ Exclusão de usuários (exceto admin principal)
- ✅ Interface responsiva e moderna
- ✅ Feedback visual com popups
- ✅ Proteção do administrador principal

### Docker
- ✅ Ambiente completo containerizado
- ✅ PHP 8.1 + Apache
- ✅ MySQL 8.0
- ✅ phpMyAdmin para administração
- ✅ Rede isolada

## 📋 Pré-requisitos

- Docker
- Docker Compose
- Git

## 🛠️ Instalação

1. **Clone o repositório**
   ```bash
   git clone <url-do-repositorio>
   cd task-manager
   ```

2. **Suba os containers**
   ```bash
   docker-compose up -d
   ```

3. **Aguarde a inicialização**
   - O banco de dados será criado automaticamente
   - As tabelas serão populadas com dados de exemplo
   - A aplicação estará disponível em `http://localhost:9000`

## 🌐 Acessos

- **Aplicação Principal**: http://localhost:9000
- **Painel de Administração**: http://localhost:9000/admin/
- **phpMyAdmin**: http://localhost:8080
  - Usuário: `task_user`
  - Senha: `task_password`

## 🔐 Painel de Administração

### Acesso
- **URL**: http://localhost:9000/admin/
- **Login**: Tela de login dedicada e independente
- **Credenciais**: `admin` / `admin123`
- **Requisito**: Usuário deve ter ID 1 ou username 'admin'

### Funcionalidades
- ✅ **Sistema de autenticação independente**:
  - Tela de login exclusiva para administradores
  - Não requer login prévio na aplicação principal
  - Sessão separada do sistema principal
  - Logout independente com redirecionamento

- ✅ **Dashboard com estatísticas**:
  - Total de usuários cadastrados
  - Total de tarefas no sistema
  - Tarefas pendentes e concluídas
  - Cards com indicadores visuais

- ✅ **Gerenciamento de usuários**:
  - Lista completa de usuários
  - Informações detalhadas (email, data de cadastro)
  - Estatísticas de tarefas por usuário
  - Avatar personalizado para cada usuário
  - Status de atividade

- ✅ **Ações administrativas**:
  - Deletar usuários (com confirmação)
  - Exclusão em cascata (remove tarefas do usuário)
  - Modal de confirmação com alertas de segurança

- ✅ **Interface responsiva**:
  - Design moderno com cores da Tecsa Group
  - Totalmente responsivo (desktop, tablet, mobile)
  - Animações e transições suaves
  - Sistema de notificações toast
  - Tela de login elegante com gradientes

### Segurança
- Sistema de autenticação dedicado e independente
- Verificação de privilégios administrativos
- Controle de acesso baseado em ID ou username
- Sessões seguras isoladas da aplicação principal
- Proteção contra acesso não autorizado
- **Proteção do administrador principal**: O usuário admin (ID 1) não pode ser excluído
- **Interface adaptativa**: Botão de exclusão é substituído por ícone de proteção para o admin principal

## 📚 API Endpoints

### Autenticação
```
POST /api/auth/login     - Login de usuário
POST /api/auth/register  - Registro de usuário
```

### Tarefas (requer autenticação)
```
GET    /api/tasks           - Listar todas as tarefas
GET    /api/tasks/:id       - Buscar tarefa por ID
POST   /api/tasks           - Criar nova tarefa
PUT    /api/tasks/:id       - Atualizar tarefa
DELETE /api/tasks/:id       - Excluir tarefa
PATCH  /api/tasks/:id/status - Atualizar status da tarefa
```

## 🧪 Testes

### Executar testes
```bash
# Dentro do container
docker-compose exec web composer test

# Ou diretamente
docker-compose exec web php vendor/bin/phpunit
```

### Cobertura de testes
```bash
docker-compose exec web composer test-coverage
```

## 📁 Estrutura do Projeto

```
task-manager/
├── src/                    # Código fonte
│   ├── admin/             # Painel de administração
│   │   ├── assets/        # CSS e JS específicos do admin
│   │   ├── index.php      # Painel principal do admin
│   │   └── login.php      # Tela de login do admin
│   ├── api/               # API REST
│   ├── assets/            # Frontend (CSS, JS)
│   ├── config/            # Configurações
│   ├── models/            # Modelos de dados
│   └── utils/             # Utilitários
├── tests/                 # Testes unitários
├── database/              # Scripts SQL
├── apache/                # Configuração Apache
├── docker-compose.yml     # Configuração Docker
├── Dockerfile             # Imagem Docker
└── README.md             # Documentação
```

## 🔧 Configuração

### Variáveis de Ambiente
As variáveis estão definidas no `docker-compose.yml`:

```yaml
DB_HOST: db
DB_NAME: task_manager
DB_USER: task_user
DB_PASS: task_password
```

### Banco de Dados
- **Host**: `db` (dentro da rede Docker)
- **Porta**: `3306`
- **Database**: `task_manager`
- **Usuário**: `task_user`
- **Senha**: `task_password`

## 🚀 Funcionalidades Implementadas

### ✅ Requisitos Obrigatórios
- [x] API RESTful com todas as rotas solicitadas
- [x] CRUD completo de tarefas
- [x] Interface responsiva com Bootstrap
- [x] Comunicação AJAX com a API
- [x] Ambiente Docker completo
- [x] Execução na porta 9000

### ✅ Diferenciais Implementados
- [x] Autenticação JWT
- [x] Validação de dados (frontend e backend)
- [x] Módulos ES6 organizados
- [x] Sanitização de entrada
- [x] Testes unitários
- [x] Dashboard com estatísticas
- [x] Interface moderna e intuitiva
- [x] Tratamento de erros robusto
- [x] Notificações toast
- [x] Responsividade completa
- [x] **Painel de administração completo**
- [x] **Gerenciamento de usuários**
- [x] **Sistema de autenticação avançado**
- [x] **Interface admin responsiva**
- [x] **Login independente para admin**
- [x] **Sessões isoladas por contexto**

## 🛡️ Segurança

- **Sanitização**: Todos os dados de entrada são sanitizados
- **Validação**: Validação rigorosa no frontend e backend
- **JWT**: Autenticação segura com tokens
- **SQL Injection**: Protegido com prepared statements
- **XSS**: Protegido com escape de HTML
- **CORS**: Configurado adequadamente

## 📱 Responsividade

A aplicação é totalmente responsiva e funciona em:
- Desktop
- Tablet
- Mobile

## 🔍 Debugging

### Logs do Docker
```bash
# Ver logs da aplicação
docker-compose logs web

# Ver logs do banco
docker-compose logs db

# Ver todos os logs
docker-compose logs
```

### Acessar container
```bash
# Acessar container da aplicação
docker-compose exec web bash

# Acessar banco de dados
docker-compose exec db mysql -u task_user -p task_manager
```

## 🚀 Deploy

### Produção
Para deploy em produção, recomenda-se:

1. Alterar a chave JWT em `src/utils/JWT.php`
2. Configurar HTTPS
3. Usar variáveis de ambiente para credenciais
4. Configurar backup do banco de dados
5. Implementar rate limiting

### Desenvolvimento
```bash
# Parar containers
docker-compose down

# Reconstruir containers
docker-compose up -d --build

# Limpar volumes (cuidado: apaga dados)
docker-compose down -v
```

## 🤝 Contribuição

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 👨‍💻 Autor Lucas Dev LSM

Desenvolvido como projeto de teste técnico.

**Nota**: Esta aplicação foi desenvolvida seguindo as melhores práticas de desenvolvimento web moderno, com foco em segurança, usabilidade e manutenibilidade.

## 🔒 Versão de Produção

Esta é a versão final otimizada para produção, incluindo:
- Remoção de logs de debug e console
- Proteção do administrador principal contra exclusão
- Interface adaptativa com feedback visual
- Código limpo e otimizado para GitHub

**Nota**: Esta aplicação foi desenvolvida seguindo as melhores práticas de desenvolvimento web moderno, com foco em segurança, usabilidade e manutenibilidade.

## 👤 Credenciais Padrão

### Aplicação Principal
- **Usuário**: `admin`
- **Senha**: `admin123`

### Painel de Administração  
- **Usuário**: `admin`
- **Senha**: `admin123`
- **Acesso**: http://localhost:9000/admin/ # tasks-devlsm
