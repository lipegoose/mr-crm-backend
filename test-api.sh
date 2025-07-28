#!/bin/bash

echo "🧪 Testando API do Mr.CRM..."

# Aguardar um pouco para garantir que a API está rodando
sleep 5

# Teste 1: Verificar se a API está respondendo
echo "📡 Teste 1: Verificando se a API está respondendo..."
response=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8080/)

if [ $response -eq 200 ]; then
    echo "✅ API está respondendo (Status: $response)"
else
    echo "❌ API não está respondendo (Status: $response)"
    exit 1
fi

# Teste 2: Verificar resposta da API
echo "📡 Teste 2: Verificando resposta da API..."
api_response=$(curl -s http://localhost:8080/)
echo "Resposta da API: $api_response"

# Teste 3: Testar registro de usuário
echo "📡 Teste 3: Testando registro de usuário..."
register_response=$(curl -s -X POST http://localhost:8080/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Admin Teste",
    "email": "admin@teste.com",
    "password": "123456",
    "password_confirmation": "123456"
  }')

echo "Resposta do registro: $register_response"

# Teste 4: Testar login
echo "📡 Teste 4: Testando login..."
login_response=$(curl -s -X POST http://localhost:8080/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@teste.com",
    "password": "123456"
  }')

echo "Resposta do login: $login_response"

# Extrair token do login
token=$(echo $login_response | grep -o '"access_token":"[^"]*"' | cut -d'"' -f4)

if [ ! -z "$token" ]; then
    echo "✅ Token obtido com sucesso"
    
    # Teste 5: Testar rota protegida
    echo "📡 Teste 5: Testando rota protegida..."
    protected_response=$(curl -s -X GET http://localhost:8080/auth/me \
      -H "Authorization: Bearer $token")
    
    echo "Resposta da rota protegida: $protected_response"
else
    echo "❌ Não foi possível obter o token"
fi

echo ""
echo "🎉 Testes concluídos!"
echo ""
echo "📋 URLs de acesso:"
echo "   - API: http://localhost:8080"
echo "   - Adminer: http://localhost:8081"
echo ""
echo "🔧 Para entrar no container: docker exec -it mrcrm-app bash" 