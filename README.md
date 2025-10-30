# Convenia

Este projeto utiliza **Docker** para configurar um ambiente Laravel completo, com **PHP 8.4 (FPM)**, **Nginx**, **MySQL
8**, e **Redis** para cache e filas.

## 🧱 Configuração inicial

1. Copie o arquivo `.env.example` (ou `.env`) e configure suas variáveis:

   ```bash
   cp .env.example .env
   ```

2. Inicie os serviços Docker:

   ```bash
   docker-compose up -d
   ```

3. Instale as dependências do projeto:

   ```bash
   docker-compose exec app composer install
   ```

4. Gere a chave da aplicação:

   ```bash
   docker-compose exec app php artisan key:generate
   ```

5. Execute as migrations e seeders:

   ```bash
   docker-compose exec app php artisan migrate
   docker-compose exec app php artisan db:seed
   
   # Gerar usuários
   docker-compose exec app php artisan db:seed --class=UserSeeder
   ```
6. Crie as chaves da autenticação do passport:
   ```bash
   docker-compose exec app php artisan passport:client --personal --provider=users
   ```

7. Acesse

   ```bash
   http://localhost:8000
   ```
---

## 🚀 Tecnologias

- **PHP 8.4 FPM**
- **Nginx (Alpine)**
- **MySQL 8.0**
- **Redis (latest)**
- **Composer**
- **Laravel 12+**

---
