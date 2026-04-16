# Backend

Backend em Laravel 10+ para cadastro e autenticação JWT, com regra de expiração de usuários via Service + Job + Scheduler.

## Stack

- PHP 8.1+
- Laravel 10+
- PostgreSQL
- JWT (`php-open-source-saver/jwt-auth`)
- Eloquent ORM
- Scheduler e Jobs

## Arquitetura aplicada

- Migration: tabela `usuarios`
- Model: `Usuario`
- Form Request: `RegisterUsuarioRequest`
- Controller: `AuthController`
- Service: `ExpirarUsuariosService`
- Job: `ExpirarUsuariosJob`
- Scheduler: agendamento diário no `Kernel`

## Modelo de dados

Tabela: `usuarios`

- `id` (PK)
- `nome` (string, obrigatório)
- `email` (string, único)
- `senha` (string, hash automático)
- `telefone` (string)
- `data_nascimento` (date)
- `status` (enum: `ativo` | `expirado`)
- `data_expiracao` (date)
- `created_at` e `updated_at`

## Requisitos

- PHP 8.1+
- Composer
- PostgreSQL 14+

## Execucao com Docker

O projeto possui `docker-compose.yml` com os servicos:

- `backend` (Laravel)
- `postgres` (PostgreSQL 16)

### Subir backend + banco

```bash
docker compose up --build -d
```

### Comportamento importante de rede

- Dentro do container, a API usa `DB_HOST=postgres` (nome do servico Docker).
- A porta da API e exposta por padrao somente em localhost:

```dotenv
BACKEND_BIND_HOST=127.0.0.1
APP_PORT=8000
```

### Expor API para outros dispositivos na rede local

Se o frontend estiver em outra maquina/dispositivo, suba o backend assim:

```bash
BACKEND_BIND_HOST=0.0.0.0 docker compose up --build -d
```

### Comandos uteis

```bash
docker compose logs -f backend
docker compose logs -f postgres
docker compose down
docker compose down -v
```

## Instalação

1. Clonar o projeto

```bash
git clone <url-do-repositorio>
cd backend
```

2. Instalar dependências

```bash
composer install
```

3. Configurar ambiente

```bash
cp .env.example .env
php artisan key:generate
```

4. Configurar banco e JWT no `.env`

```dotenv
APP_NAME=TriAL
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=trial_db
DB_USERNAME=postgres
DB_PASSWORD=postgres

QUEUE_CONNECTION=sync

JWT_SECRET=<gerado-pelo-comando-abaixo>
JWT_TTL=60
JWT_ALGO=HS256
```

5. Gerar segredo JWT

```bash
php artisan jwt:secret
```

6. Rodar migrations

```bash
php artisan migrate
```

7. Subir API

```bash
php artisan serve
```

## Rotas da API

Base URL local: `http://127.0.0.1:8000/api`

- `POST /register`
- `POST /login`
- `GET /me` (protegida com `auth:api`)
- `POST /logout` (protegida com `auth:api`)

## Compatibilidade de payload com frontend

Para facilitar integracao, a API aceita ambos os campos abaixo em login/cadastro:

- `senha`
- `password`

No cadastro, a senha e armazenada com hash.

## Payloads esperados

### Register

`POST /api/register`

```json
{
	"nome": "Gabriel",
	"email": "gabriel@example.com",
	"senha": "123456",
	"telefone": "11999999999",
	"data_nascimento": "1998-01-01"
}
```

Resposta esperada:

- Cria usuário com `status=ativo`
- Define `data_expiracao = hoje + 7 dias`
- Retorna `usuario` e `token`

### Login

`POST /api/login`

```json
{
	"email": "gabriel@example.com",
	"senha": "123456"
}
```

Resposta esperada:

- Retorna `usuario` e `token`

### Me (rota protegida)

`GET /api/me`

Header:

```http
Authorization: Bearer <token>
```

## Exemplos com cURL

### Registrar

```bash
curl -X POST "http://127.0.0.1:8000/api/register" \
	-H "Content-Type: application/json" \
	-d '{
		"nome":"Gabriel",
		"email":"gabriel@example.com",
		"senha":"123456",
		"telefone":"11999999999",
		"data_nascimento":"1998-01-01"
	}'
```

### Login

```bash
curl -X POST "http://127.0.0.1:8000/api/login" \
	-H "Content-Type: application/json" \
	-d '{
		"email":"gabriel@example.com",
		"senha":"123456"
	}'
```

### Me

```bash
curl -X GET "http://127.0.0.1:8000/api/me" \
	-H "Authorization: Bearer <token>"
```

## Regra de expiração (Service + Job + Scheduler)

### Regra

`ExpirarUsuariosService::executar()`:

- busca usuários com `status=ativo`
- filtra `data_expiracao < hoje`
- atualiza para `status=expirado`
- registra log no formato:

```text
[2026-04-15] ExpirarUsuariosJob: 5 usuários expirados
```

### Job

`ExpirarUsuariosJob` chama o service no `handle()`.

### Scheduler

No `app/Console/Kernel.php`, o job está agendado com:

```php
$schedule->job(new ExpirarUsuariosJob)->daily();
```

## Validando o job manualmente

### Via Tinker

```bash
php artisan tinker
```

Criar usuário expirado para teste:

```php
App\Models\Usuario::factory()->create([
	'data_expiracao' => now()->subDays(3),
	'status' => 'ativo',
]);
```

Disparar job:

```php
dispatch(new App\Jobs\ExpirarUsuariosJob());
```

Validar status:

```php
App\Models\Usuario::find(1)?->status;
// esperado: "expirado"
```

### Via scheduler

```bash
php artisan schedule:list
php artisan schedule:run
```

## Troubleshooting

### Erro `could not find driver` (PostgreSQL)

Instalar extensão do pgsql no PHP e validar:

```bash
php -m | grep -Ei "pdo|pgsql"
```

### Erro de autenticação no PostgreSQL

Conferir usuário/senha/porta no `.env`.

### Mudança de schema

Se o banco local tinha schema antigo, recrie em ambiente de desenvolvimento:

```bash
php artisan migrate:fresh
```

## Observações

- O guard padrão está configurado para JWT (`auth:api`).
- O model de autenticação é `App\Models\Usuario`.
- O campo de senha armazenado é `senha` (com hash automático por mutator).
