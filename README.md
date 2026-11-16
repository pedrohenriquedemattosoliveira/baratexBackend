# BARATEX API (Laravel)

API RESTful do ecossistema BARATEX para agendamento de serviços
automotivos. Este repositório é o porte para **PHP 8.3 + Laravel 13**
da API original, escrita em **Java 21 + Spring Boot** (Maven,
`src/main/java/com/baratex/api`).

> Veja [docs/migration-notes.md](docs/migration-notes.md) para a lista
> completa de bugs corrigidos e decisões de design tomadas durante a
> migração.

## Stack

| Camada | Original (Java) | Aqui (Laravel) |
|---|---|---|
| Framework | Spring Boot 4 | Laravel 13 |
| Banco | PostgreSQL + PostGIS | MySQL/MariaDB (XAMPP) — ver [docs/mysql.md](docs/mysql.md) |
| ORM | Hibernate/JPA | Eloquent |
| Autenticação | JWT manual (JJWT) + filtro Spring Security | JWT manual (`firebase/php-jwt`) + guard dedicado |
| Tempo real | WebSocket STOMP/SockJS | Laravel Broadcasting (Reverb) — ver [docs/websockets.md](docs/websockets.md) |
| Documentação da API | springdoc-openapi (`/swagger-ui.html`) | Swagger UI servindo `resources/docs/openapi.yaml` (`/docs`) |

## Requisitos

- PHP 8.3+ com as extensões `pdo_mysql`, `mysqli`, `mbstring`, `openssl`, `curl`, `zip`
- Composer 2
- MySQL/MariaDB — por exemplo, o MySQL que vem no **XAMPP** (ver [docs/mysql.md](docs/mysql.md))

## Instalação

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Com o Apache + MySQL do XAMPP rodando, crie o banco `baratex_db` (veja
[docs/mysql.md](docs/mysql.md)) e ajuste `.env` se seu usuário/senha do
MySQL não forem os padrões do XAMPP (`root`, sem senha, porta `3306`).
Depois:

```bash
php artisan migrate
```

Para desenvolvimento rápido sem MySQL (perdendo apenas
`GET /api/oficinas/proximas`), troque no `.env`:

```env
DB_CONNECTION=sqlite
```

## Executando

```bash
php artisan serve
```

A API fica em `http://localhost:8000/api/...` e a documentação
interativa (Swagger UI) em `http://localhost:8000/docs`.

Para o chat em tempo real (opcional — funciona em modo "log" por
padrão), ver [docs/websockets.md](docs/websockets.md).

## Testes

```bash
php artisan test
```

A suíte roda em SQLite em memória (configurado em `phpunit.xml`) e
cobre: autenticação (registro/login/JWT), o fluxo completo de
agendamento (oficina → serviço → motorista → veículo → agendamento →
status → avaliação) e a busca de marcas/modelos/anos de veículos.

## Autenticação

Stateless, via **JWT no header `Authorization: Bearer <token>`** —
sem sessão, sem cookies (equivalente a
`SessionCreationPolicy.STATELESS` + `JwtAuthenticationFilter` da API
original).

```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"nome":"João","email":"joao@example.com","senha":"123456","cpf":"12345678900","tipoUsuario":"MOTORISTA"}'

curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"joao@example.com","senha":"123456"}'
# => { "token": "...", "tipo": "Bearer", ... }

curl http://localhost:8000/api/veiculos \
  -H "Authorization: Bearer <token>"
```

### Rotas públicas (sem token)

- `POST /api/auth/login`, `POST /api/auth/register`
- `GET /api/oficinas`, `GET /api/oficinas/{id}`, `GET /api/oficinas/proximas`
- `GET /api/servicos/oficina/{oficinaId}`
- `GET /api/modelos-veiculos/marcas|modelos|anos`

**Todo o restante exige JWT** — inclusive `POST /api/oficinas`,
`POST /api/servicos` e `POST /api/motoristas` (fiel à regra
`anyRequest().authenticated()` da API original; ver item 7 em
[docs/migration-notes.md](docs/migration-notes.md)).

## Documentação da API

- **Interativa:** `/docs` (Swagger UI, lê `resources/docs/openapi.yaml`)
- **Arquivo-fonte:** [resources/docs/openapi.yaml](resources/docs/openapi.yaml) — OpenAPI 3.0, todos os endpoints, schemas e respostas de erro

## Domínio

```
Usuario (MOTORISTA | OFICINA | ADMIN)
  ├─ Motorista ──< Veiculo >── ModeloVeiculo ──< Marca
  │                  └─< Agendamento >── Oficina ──< Servico
  └─ Oficina ── Endereco (latitude/longitude; busca por proximidade via Haversine)

Agendamento ──< ItemAgendamento >── Servico
Agendamento ── Avaliacao (1:1)
Agendamento ──< MensagemChat >── Usuario (remetente)
Agendamento ──< GaragemVirtual
```

- **Models:** `app/Models/*`
- **Regras de negócio:** `app/Services/*` (um service por domínio, assim como a API original)
- **Validação:** `app/Http/Requests/*` (equivalentes aos DTOs com Bean Validation)
- **Formato de resposta:** `app/Http/Resources/*` (equivalentes aos Response DTOs)
- **Autenticação JWT:** `app/Services/JwtService.php`, `app/Auth/JwtGuard.php`
- **Erros:** `bootstrap/app.php` (`withExceptions`) + `app/Exceptions/ErroResposta.php` — mesmo formato de erro da API original (`timestamp`, `status`, `erro`, `mensagem`, `path`, `errosValidacao`)

## Variáveis de ambiente relevantes

| Variável | Equivalente original | Descrição |
|---|---|---|
| `JWT_SECRET` | `api.jwt.secret` | Segredo HMAC do JWT |
| `JWT_TTL_MINUTES` | `api.jwt.expiration` (ms) | Validade do token, em minutos |
| `DB_*` | `spring.datasource.*` | Conexão com o MySQL/MariaDB |
| `BROADCAST_CONNECTION` | — (STOMP embutido) | `log` (padrão) ou `reverb` para WebSocket real |
