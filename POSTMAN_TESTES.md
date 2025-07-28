# 🚀 Testes da API Mr.CRM no Postman

## 📋 Pré-requisitos

1. **Servidor rodando**: Certifique-se de que o servidor Laravel está rodando
2. **Banco configurado**: Migrations executadas e banco conectado
3. **Usuário criado**: Execute o comando para criar o primeiro usuário

## 🔧 Criando o Primeiro Usuário

Execute o comando Artisan para criar o primeiro usuário administrador:

```bash
# Opção 1: Comando interativo
php artisan user:create-admin

# Opção 2: Comando com parâmetros
php artisan user:create-admin --name="Admin" --email="admin@mrcrm.com" --password="123456"
```

## 📡 Configuração do Postman

### 1. **Variáveis de Ambiente**
Crie uma nova collection no Postman e configure as variáveis:

- `base_url`: `http://localhost:8000` (ou sua URL)
- `token`: (será preenchido automaticamente após login)

### 2. **Headers Padrão**
Configure os seguintes headers para todas as requisições:

```
Content-Type: application/json
Accept: application/json
```

Para rotas protegidas, adicione:
```
Authorization: Bearer {{token}}
```

## 🧪 Testes das Rotas

### 1. **Teste da API (Rota Pública)**
```
GET {{base_url}}/
```

**Resposta esperada:**
```json
{
    "message": "Mr.CRM API",
    "version": "1.0.0",
    "status": "running"
}
```

### 2. **Login (Rota Pública)**
```
POST {{base_url}}/auth/login
```

**Body (JSON):**
```json
{
    "email": "admin@mrcrm.com",
    "password": "123456"
}
```

**Resposta esperada:**
```json
{
    "success": true,
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "bearer",
    "expires_in": 86400,
    "user": {
        "id": 1,
        "name": "Admin",
        "email": "admin@mrcrm.com",
        "status": true,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

**Script para capturar o token:**
```javascript
if (pm.response.code === 200) {
    const response = pm.response.json();
    pm.environment.set("token", response.access_token);
}
```

### 3. **Registro de Usuário (Rota Pública)**
```
POST {{base_url}}/auth/register
```

**Body (JSON):**
```json
{
    "name": "João Silva",
    "email": "joao@exemplo.com",
    "password": "123456",
    "password_confirmation": "123456"
}
```

### 4. **Dados do Usuário Logado (Rota Protegida)**
```
GET {{base_url}}/auth/me
```

**Headers:**
```
Authorization: Bearer {{token}}
```

### 5. **Renovar Token (Rota Protegida)**
```
POST {{base_url}}/auth/refresh
```

**Headers:**
```
Authorization: Bearer {{token}}
```

### 6. **Logout (Rota Protegida)**
```
POST {{base_url}}/auth/logout
```

**Headers:**
```
Authorization: Bearer {{token}}
```

### 7. **Teste de Rota Protegida**
```
GET {{base_url}}/test
```

**Headers:**
```
Authorization: Bearer {{token}}
```

**Resposta esperada:**
```json
{
    "message": "Rota protegida funcionando!",
    "user": {
        "id": 1,
        "name": "Admin",
        "email": "admin@mrcrm.com",
        "status": true
    }
}
```

## 🔍 Casos de Teste

### **Cenário 1: Login com Credenciais Válidas**
1. Execute o login com email e senha corretos
2. Verifique se retorna o token JWT
3. Teste uma rota protegida com o token

### **Cenário 2: Login com Credenciais Inválidas**
1. Execute o login com email ou senha incorretos
2. Verifique se retorna erro 401

### **Cenário 3: Acesso a Rota Protegida sem Token**
1. Tente acessar `/auth/me` sem o header Authorization
2. Verifique se retorna erro 401

### **Cenário 4: Token Expirado/Inválido**
1. Use um token inválido ou expirado
2. Verifique se retorna erro 401

### **Cenário 5: Registro de Usuário**
1. Registre um novo usuário
2. Faça login com as credenciais do novo usuário
3. Verifique se funciona normalmente

## ⚠️ Possíveis Erros e Soluções

### **Erro 500 - JWT Secret não configurado**
```bash
php artisan jwt:secret
```

### **Erro de Conexão com Banco**
Verifique o arquivo `.env` e execute:
```bash
php artisan migrate
```

### **Erro de CORS**
Verifique se o middleware CORS está configurado corretamente.

### **Token não está sendo enviado**
Verifique se o script de captura do token está funcionando.

## 📊 Validação de Respostas

### **Códigos de Status HTTP:**
- `200`: Sucesso
- `201`: Criado com sucesso
- `400`: Dados inválidos
- `401`: Não autorizado
- `422`: Erro de validação
- `500`: Erro interno do servidor

### **Estrutura de Resposta de Sucesso:**
```json
{
    "success": true,
    "data": {...},
    "message": "Mensagem de sucesso"
}
```

### **Estrutura de Resposta de Erro:**
```json
{
    "success": false,
    "message": "Mensagem de erro",
    "errors": {...}
}
```

## 🎯 Checklist de Testes

- [ ] API está respondendo (`GET /`)
- [ ] Login funciona com credenciais válidas
- [ ] Login falha com credenciais inválidas
- [ ] Registro de usuário funciona
- [ ] Rotas protegidas funcionam com token válido
- [ ] Rotas protegidas falham sem token
- [ ] Renovação de token funciona
- [ ] Logout funciona
- [ ] Token é invalidado após logout

---

**🎉 Parabéns! Sua API está funcionando corretamente!** 