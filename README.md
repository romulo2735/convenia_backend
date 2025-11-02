# Convenia

Projeto Laravel com Docker (PHP 8.4 FPM, Nginx, MySQL 8, Redis) para gestão de colaboradores, importação assíncrona e autenticação via Passport.

## 🧱 Instalação e Setup Rápido

1. Copiar variáveis de ambiente

```bash
cp .env.example .env
```

1. Subir os serviços Docker

```bash
docker compose up -d
```

1. Instalar dependências e gerar chave

```bash
docker compose exec app composer install
docker compose exec app php artisan key:generate
```

1. Migrar e popular o banco

```bash
docker compose exec app php artisan migrate
# Popula com usuários e colaboradores
docker compose exec app php artisan db:seed
# (Opcional) seeders específicos
docker compose exec app php artisan db:seed --class=UserSeeder
```

1. Configurar Passport (autenticação OAuth2)

```bash
docker compose exec app php artisan passport:keys
docker compose exec app php artisan passport:client --personal --provider=users
```

1. Fila para processamento de importações (opcional)

```bash
docker compose exec app php artisan queue:work
```

1. Acessar a aplicação

```text
http://localhost:8000
```

## 🧪 Testes e Cobertura

- Executar testes:

```bash
docker compose exec app php artisan test
```

- Gerar cobertura:

```bash
docker compose exec -e XDEBUG_MODE=coverage app php vendor/bin/phpunit \
  --coverage-html coverage --coverage-text --coverage-clover coverage/clover.xml
```

Abra `coverage/index.html` para visualizar o relatório.

## 📦 Seeds e Fábricas

- `database/seeders/DatabaseSeeder.php`: chama `UserSeeder` e `CollaboratorSeeder` e cria um usuário base "Convenia Teste User".
- `database/seeders/UserSeeder.php`: cria usuários via factory.
- `database/seeders/CollaboratorSeeder.php`: cria colaboradores para cada usuário.
- `database/factories/CollaboratorFactory.php`: dados realistas para colaboradores.

## 🔐 Autenticação

- Autenticação por token pessoal do Passport.
- Após criar um token pessoal, usar o header:

```text
Authorization: Bearer {TOKEN}
```

## 📘 API

Base URL: `http://localhost:8000/api`

### Auth

- POST `/register`
  - body (json): `{ name, email, password }`
  - resposta: usuário + token
- POST `/login`
  - body (json): `{ email, password }`
  - resposta: usuário + token
- GET `/user` (auth)
  - retorna usuário autenticado

### Colaboradores (auth:api)

- GET `/collaborators`
  - query params: `search` (nome/email/cpf), `sort_by` (`name|email|cpf|created_at`), `sort_dir` (`asc|desc`), `per_page`
  - resposta: paginação de colaboradores do usuário
- POST `/collaborators`
  - body (json): `{ name, email, cpf, city, state }`
  - resposta: colaborador criado
- GET `/collaborators/{id}`
  - resposta: colaborador
- PUT/PATCH `/collaborators/{id}`
  - body (json): campos parciais ou completos (`name`, `email`, `cpf`, `city`, `state`)
  - resposta: colaborador atualizado
- DELETE `/collaborators/{id}`
  - resposta: `{ message }`
- POST `/collaborators/import`
  - multipart form-data: `file` (csv/xlsx, máx 4MB)
  - resposta imediata: `{ success, message }`
  - processamento em segundo plano; e-mail enviado na conclusão.

## 📄 Postman

Coleção disponível: `collection.json`

## 🚀 Tecnologias

- PHP 8.4 FPM
- Nginx (Alpine)
- MySQL 8.0
- Redis (latest)
- Composer
- Laravel 12+
