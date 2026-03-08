# Password Vault

> https://github.com/mar3q/password-vault

A REST API for managing encrypted credentials, built as a showcase of clean architecture patterns in PHP.

**Stack:** PHP 8.4 · Symfony 8.0 · Doctrine ORM · MariaDB · Docker

---

## Architecture

The project applies **Hexagonal Architecture** (Ports & Adapters) combined with **CQRS**, organized into bounded contexts. Each module is self-contained and follows the same layered structure:

```
src/<Module>/
├── Domain/          # Aggregates, Value Objects, Domain Events, Port interfaces
├── Application/     # Commands, Queries, Handlers, DTOs
├── Infrastructure/  # Doctrine repositories, framework adapters, concrete impls
└── Presentation/    # HTTP controllers, CLI commands
```

### Modules

| Module | Responsibility |
|--------|---------------|
| `Identity` | User registration, authentication, profile management |
| `Vault` | CRUD for encrypted credentials (passwords, logins, URLs) |
| `Shared` | Cross-cutting concerns: CommandBus, QueryBus, HTTP helpers |

### Key design decisions

**Autowire and autoconfigure are disabled.** Every service is explicitly registered in `config/services.yaml`. This makes dependencies visible and intentional — no magic, no surprises when the container grows.

**Invokable single-action controllers.** Each controller does exactly one thing. They are plain classes with no framework coupling — no `AbstractController`, no `$this->json()`, no inherited state.

**Symfony Messenger as the command/query bus.** The application layer knows nothing about Messenger — it only depends on `CommandBus` and `QueryBus` port interfaces. The Messenger implementation lives in Infrastructure and can be swapped without touching a single handler.

**Domain events via ports.** Aggregates collect events internally and release them through `releaseEvents()`. Handlers dispatch them via the `EventDispatcher` port, keeping the domain completely framework-free.

**Sodium encryption.** Vault entries are encrypted at rest with `sodium_crypto_secretbox` (XSalsa20-Poly1305). Each ciphertext includes a random nonce prepended before the MAC — encryption is authenticated and nonce is never reused.

**Value Objects for all identity.** `UserId`, `Email`, `Username`, `HashedPassword`, `EntryId`, `EntryTitle`, `OwnerId` — primitive obsession is avoided throughout the domain. Each VO validates its invariants on construction and maps to a custom Doctrine type.

---

## Getting started

### Prerequisites

- Docker & Docker Compose
- Make

### First run

```bash
git clone https://github.com/mar3q/password-vault.git
cd password-vault

make start          # Build and start containers (PHP-FPM + Nginx + MariaDB)
make app-install    # Install Composer dependencies
make db-migrate     # Create databases and run migrations
```

API is available at **http://localhost:8081/api/doc** (Swagger UI).

### Creating a user

Register via the API:

```http
POST /api/users
Content-Type: application/json

{"email": "you@example.com", "username": "you", "password": "secret"}
```

Or via the CLI (e.g. for seeding environments):

```bash
make sh
bin/console app:user:create
```

Then obtain a JWT token:

```http
POST /api/login
Content-Type: application/json

{"username": "...", "password": "..."}
```

Use the returned `token` as `Authorization: Bearer <token>` on all subsequent requests.

---

## API endpoints

| Method | Path | Description |
|--------|------|-------------|
| `POST` | `/api/login` | Obtain JWT token |
| `POST` | `/api/users` | Register a new account |
| `GET` | `/api/users/{id}` | Get user profile |
| `PATCH` | `/api/users/{id}/email` | Change email |
| `PATCH` | `/api/users/{id}/password` | Change password |
| `GET` | `/api/vault` | List vault entries (authenticated user) |
| `POST` | `/api/vault` | Create a vault entry |
| `GET` | `/api/vault/{id}` | Get a single entry |
| `PUT` | `/api/vault/{id}` | Update an entry |
| `DELETE` | `/api/vault/{id}` | Delete an entry |

Full request/response schemas are available in the Swagger UI.

---

## Testing

Three levels of tests, all executed via PHPUnit:

| Layer | Location | What it tests |
|-------|----------|---------------|
| Unit | `tests/Unit/` | Domain model, Value Objects, Doctrine types, encrypter |
| Application | `tests/Application/` | Command handlers with mocked ports |
| HTTP | `tests/Http/` | Full request–response cycle against the test database |

```bash
make qa-test        # Run full test suite
make qa             # Tests + PHPStan + code style check
```

The HTTP test suite uses Symfony's `KernelBrowser` with a dedicated `vault_test` database. Each test class manages its own fixtures — no shared mutable state between tests.

---

## Code quality

| Tool | Configuration | Scope |
|------|--------------|-------|
| PHPStan | level 6, `phpstan.dist.neon` | `src/` + `tests/` |
| php-cs-fixer | `@auto` ruleset, `risky: false` | entire codebase |

```bash
make qa-stan        # Static analysis
make qa-cs          # Check style
make qa-cs-fix      # Auto-fix style
```

---

## Database migrations

Migrations live in `migrations/`, managed by Doctrine Migrations.

```bash
make db-migrate     # Apply pending migrations (creates DBs if missing)
make db-status      # Show migration status
make db-validate    # Validate mapping vs schema
```

**Adding a migration:**

1. Edit the Doctrine XML mapping in `src/*/Infrastructure/Persistence/Mapping/`
2. `make db-diff` — generates migration SQL from the diff
3. Review the generated file in `migrations/`
4. `make db-migrate` — apply

---

## Docker services

| Service | Image | Port |
|---------|-------|------|
| `php` | PHP 8.4-FPM (custom) | — |
| `nginx` | nginx:1.25-alpine | `8081` |
| `db` | mariadb:10.11 | `33061` |

```bash
make start          # docker compose up -d
make stop           # docker compose down
make restart        # stop + start
make sh             # shell into the PHP container
make docker-build   # rebuild the PHP image
```

All Make targets pass the host UID/GID into Docker so generated files (migrations, cache) are owned by the current user.

---

## Environment variables

| Variable | Description |
|----------|-------------|
| `DATABASE_URL` | Doctrine DSN |
| `JWT_SECRET_KEY` / `JWT_PUBLIC_KEY` | RSA key paths for LexikJWT |
| `JWT_PASSPHRASE` | Passphrase for the JWT private key |
| `VAULT_ENCRYPTION_KEY` | Base64-encoded 32-byte key for Sodium (`sodium_crypto_secretbox`) |

Generate an encryption key:

```bash
php -r "echo base64_encode(random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES)) . PHP_EOL;"
```
