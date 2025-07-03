#!/bin/bash

# Script para executar testes do Gerenciador de Tarefas

echo "🧪 Executando testes do Gerenciador de Tarefas..."
echo ""

# Verificar se o Docker está rodando
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker não está rodando. Inicie o Docker primeiro."
    exit 1
fi

# Verificar se os containers estão rodando
if ! docker-compose ps | grep -q "Up"; then
    echo "⚠️  Containers não estão rodando. Iniciando..."
    docker-compose up -d
    echo "⏳ Aguardando inicialização..."
    sleep 10
fi

echo "📦 Instalando dependências do Composer..."
docker-compose exec -T web composer install --no-interaction

echo ""
echo "🚀 Executando testes..."
docker-compose exec -T web php vendor/bin/phpunit --colors=always

echo ""
echo "✅ Testes concluídos!" 