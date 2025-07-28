#!/bin/bash

echo "🚀 Iniciando setup do Mr.CRM Backend..."

# Verificar se o Docker está rodando
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker não está rodando. Por favor, inicie o Docker e tente novamente."
    exit 1
fi

# Construir e iniciar os containers
echo "📦 Construindo e iniciando containers Docker..."
docker-compose up -d --build

# Aguardar o banco de dados estar pronto
echo "⏳ Aguardando banco de dados estar pronto..."
sleep 10

# Entrar no container e instalar Lumen
echo "🔧 Instalando Lumen Framework..."
docker exec -it mrcrm-app bash -c "
    composer create-project --prefer-dist laravel/lumen . --no-interaction
    composer require tymon/jwt-auth
    php artisan vendor:publish --provider=\"Tymon\JWTAuth\Providers\LumenServiceProvider\"
    php artisan jwt:secret
    php artisan key:generate
"

# Copiar arquivo .env
echo "📝 Configurando arquivo .env..."
docker exec -it mrcrm-app bash -c "cp env.local .env"

# Configurar permissões
echo "🔐 Configurando permissões..."
docker exec -it mrcrm-app bash -c "chmod -R 755 storage bootstrap/cache"

# Executar migrations
echo "🗄️ Executando migrations..."
docker exec -it mrcrm-app bash -c "php artisan migrate"

echo "✅ Setup concluído!"
echo ""
echo "🌐 URLs de acesso:"
echo "   - API: http://localhost:8080"
echo "   - Adminer: http://localhost:8081"
echo ""
echo "📋 Próximos passos:"
echo "   1. Acesse http://localhost:8080 para verificar se a API está funcionando"
echo "   2. Acesse http://localhost:8081 para gerenciar o banco de dados"
echo "   3. Configure as rotas de autenticação no arquivo routes/web.php"
echo ""
echo "🔧 Para entrar no container: docker exec -it mrcrm-app bash" 