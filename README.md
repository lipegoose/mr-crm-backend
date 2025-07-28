# Mr.CRM Backend

Backend do sistema Mr.CRM desenvolvido com Lumen Framework e autenticação JWT.

## 🚀 Características

- **Framework**: Lumen 10.x (Laravel Micro-framework)
- **PHP**: 8.2
- **Banco de Dados**: MariaDB 10.11
- **Autenticação**: JWT (JSON Web Tokens)
- **CORS**: Configurado para domínios específicos
- **Docker**: Ambiente completo containerizado
- **Adminer**: Interface web para gerenciamento do banco

## 📋 Pré-requisitos

- Docker
- Docker Compose
- Git

## 🛠️ Instalação

### 1. Clone o repositório
```bash
git clone <url-do-repositorio>
cd mr-crm-backend
```

### 2. Execute o script de instalação
```bash
chmod +x setup.sh
./setup.sh
```

O script irá:
- Construir e iniciar os containers Docker
- Instalar o Lumen Framework
- Configurar o JWT
- Executar as migrations
- Configurar permissões

### 3. Acesso aos serviços

- **API**: http://localhost:8080
- **Adminer**: http://localhost:8081
- **Banco de Dados**: localhost:3306

## 🔧 Configuração Manual (Alternativa)

Se preferir configurar manualmente:

### 1. Construir containers
```bash
docker-compose up -d --build
```

### 2. Instalar Lumen
```bash
docker exec -it mrcrm-app bash
composer create-project --prefer-dist laravel/lumen .
composer require tymon/jwt-auth
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LumenServiceProvider"
php artisan jwt:secret
php artisan key:generate
```

### 3. Configurar arquivo .env
```bash
cp env.local .env
```

### 4. Executar migrations
```bash
php artisan migrate
```

## 🔐 Autenticação JWT

### Endpoints disponíveis:

- `POST /auth/login` - Login
- `POST /auth/register` - Registro
- `POST /auth/logout` - Logout (protegido)
- `POST /auth/refresh` - Renovar token (protegido)
- `GET /auth/me` - Dados do usuário (protegido)

### Exemplo de uso:

#### Login
```bash
curl -X POST http://localhost:8080/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@mrcrm.com",
    "password": "123456"
  }'
```

#### Acesso a rota protegida
```bash
curl -X GET http://localhost:8080/auth/me \
  -H "Authorization: Bearer SEU_TOKEN_AQUI"
```

## 🌐 CORS

O sistema está configurado para aceitar requisições dos seguintes domínios:

- `http://localhost:5173` (Desenvolvimento)
- `https://bhelite.mrcrm.com.br` (Produção)

## 📁 Estrutura do Projeto

```
mr-crm-backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── AuthController.php
│   │   └── Middleware/
│   │       └── CorsMiddleware.php
│   └── Models/
│       └── User.php
├── config/
│   ├── app.php
│   ├── auth.php
│   └── jwt.php
├── database/
│   └── migrations/
│       └── 2024_01_01_000000_create_users_table.php
├── routes/
│   └── web.php
├── bootstrap/
│   └── app.php
├── docker-compose.yml
├── Dockerfile
├── setup.sh
└── README.md
```

## 🐳 Comandos Docker Úteis

```bash
# Entrar no container da aplicação
docker exec -it mrcrm-app bash

# Ver logs dos containers
docker-compose logs

# Parar todos os containers
docker-compose down

# Reconstruir containers
docker-compose up -d --build

# Limpar volumes (cuidado: apaga dados do banco)
docker-compose down -v
```

## 🔄 Deploy na Hostinger

Para deploy na Hostinger (sem Docker):

1. Faça upload dos arquivos via FTP/SFTP
2. Execute no servidor:
```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan jwt:secret
php artisan migrate
```

3. Configure o arquivo `.env` com as credenciais de produção
4. Configure o servidor web (Apache/Nginx) para apontar para a pasta `public`

## 🚨 Configurações de Segurança

- JWT Secret gerado automaticamente
- CORS configurado apenas para domínios permitidos
- Senhas hasheadas com bcrypt
- Tokens com expiração de 24 horas
- Refresh tokens com expiração de 30 dias

## 📝 Variáveis de Ambiente

Principais variáveis no arquivo `.env`:

```env
APP_NAME=Mr.CRM
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8080

DB_CONNECTION=mysql
DB_HOST=db
DB_DATABASE=mrcrm
DB_USERNAME=mrcrm
DB_PASSWORD=mrcrm

JWT_SECRET=seu-jwt-secret
JWT_TTL=1440
JWT_REFRESH_TTL=43200

CORS_ALLOWED_ORIGINS=http://localhost:5173,https://bhelite.mrcrm.com.br
```

## 🤝 Contribuição

1. Faça um fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo `LICENSE` para mais detalhes.

## 🆘 Suporte

Para suporte, envie um email para contato@mrgoose.com.br ou abra uma issue no repositório. 


# Lumen PHP Framework

[![Build Status](https://travis-ci.org/laravel/lumen-framework.svg)](https://travis-ci.org/laravel/lumen-framework)
[![Total Downloads](https://img.shields.io/packagist/dt/laravel/lumen-framework)](https://packagist.org/packages/laravel/lumen-framework)
[![Latest Stable Version](https://img.shields.io/packagist/v/laravel/lumen-framework)](https://packagist.org/packages/laravel/lumen-framework)
[![License](https://img.shields.io/packagist/l/laravel/lumen)](https://packagist.org/packages/laravel/lumen-framework)

Laravel Lumen is a stunningly fast PHP micro-framework for building web applications with expressive, elegant syntax. We believe development must be an enjoyable, creative experience to be truly fulfilling. Lumen attempts to take the pain out of development by easing common tasks used in the majority of web projects, such as routing, database abstraction, queueing, and caching.

> **Note:** In the years since releasing Lumen, PHP has made a variety of wonderful performance improvements. For this reason, along with the availability of [Laravel Octane](https://laravel.com/docs/octane), we no longer recommend that you begin new projects with Lumen. Instead, we recommend always beginning new projects with [Laravel](https://laravel.com).

## Official Documentation

Documentation for the framework can be found on the [Lumen website](https://lumen.laravel.com/docs).

## Contributing

Thank you for considering contributing to Lumen! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Security Vulnerabilities

If you discover a security vulnerability within Lumen, please send an e-mail to Taylor Otwell at taylor@laravel.com. All security vulnerabilities will be promptly addressed.

## License

The Lumen framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
