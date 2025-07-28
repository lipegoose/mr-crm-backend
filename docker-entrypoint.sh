#!/bin/bash

# Verificar se o Lumen está instalado
if [ ! -f "public/index.php" ]; then
    echo "🚀 Lumen não encontrado. Instalando..."
    
    # Instalar Lumen em um diretório temporário
    composer create-project --prefer-dist laravel/lumen /tmp/lumen --no-interaction
    
    # Copiar arquivos do Lumen para o diretório atual
    cp -r /tmp/lumen/* .
    cp -r /tmp/lumen/.* . 2>/dev/null || true
    
    # Limpar diretório temporário
    rm -rf /tmp/lumen
    
    # Instalar JWT
    composer require tymon/jwt-auth
    
    # Publicar configurações do JWT
    php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LumenServiceProvider"
    
    # Gerar chaves
    php artisan jwt:secret
    php artisan key:generate
    
    # Copiar arquivo .env
    if [ -f "env.local" ]; then
        cp env.local .env
    fi
    
    # Configurar permissões
    chmod -R 755 storage bootstrap/cache
    
    # Executar migrations se o banco estiver disponível
    echo "⏳ Aguardando banco de dados..."
    sleep 10
    
    # Tentar executar migrations
    php artisan migrate --force 2>/dev/null || echo "⚠️ Migrations não executadas (banco pode não estar pronto)"
    
    echo "✅ Lumen instalado com sucesso!"
else
    echo "✅ Lumen já está instalado"
fi

# Iniciar servidor PHP
echo "🌐 Iniciando servidor PHP..."
exec php -S 0.0.0.0:8000 -t public public/index.php 