# Password Vault

REST API built with Symfony 8.0, PHP 8.4, Docker (PHP-FPM + Nginx + MariaDB).

## Quick start

```bash
make start                # Start Docker containers
make app-install          # Install PHP dependencies
make db-migrate           # Create database schema
```

API: http://localhost:8081/api/doc (Swagger UI)

## Setup from scratch

1. Clone the repository
2. `make start` — start containers (first run builds the PHP image)
3. `make app-install` — install dependencies
4. `make db-migrate` — run migrations

## Make targets

| Target | Alias | Description |
|--------|-------|-------------|
| **Docker** | | |
| `docker-up` | `start` | Start containers |
| `docker-down` | `stop` | Stop containers |
| `docker-restart` | `restart` | Restart containers |
| `docker-build` | | Rebuild PHP image |
| `docker-sh` | `sh` | Shell into PHP container |
| **Dependencies** | | |
| `app-install` | | `composer install` |
| **Database** | | |
| `db-create` | | Create dev + test databases (idempotent) |
| `db-migrate` | | Create databases + apply pending migrations |
| `db-status` | | Show migration status |
| `db-diff` | | Generate migration from mapping diff |
| `db-validate` | | Validate mapping vs DB schema |
| **Quality** | | |
| `qa` | | Run all checks below |
| `qa-stan` | | PHPStan (level 6) |
| `qa-cs` | | Check code style (php-cs-fixer) |
| `qa-cs-fix` | | Auto-fix code style |
| `qa-test` | | PHPUnit tests |

All commands pass host UID/GID to Docker so generated files keep correct ownership.

## Database migrations

Migrations live in `migrations/`, managed by Doctrine Migrations.

On a fresh database `make db-migrate` creates both databases (`vault` and `vault_test`)
and applies all migrations.

### Adding a new migration

1. Update Doctrine XML mapping in `src/*/Infrastructure/Persistence/Mapping/`
2. `make db-diff` — generates a migration file
3. Review the generated SQL
4. `make db-migrate` — apply

## Architecture

Hexagonal architecture (ports & adapters) with CQRS, organized by module:

```
src/<Module>/
├── Domain/           Value Objects, Aggregates, Events, Ports, Exceptions
├── Application/      Commands, Queries, Handlers, DTOs
├── Infrastructure/   Doctrine repositories, framework adapters
└── Presentation/     Controllers (invokable, #[Route] attributes)
```

Autowire and autoconfigure are disabled — every service is explicitly
registered in `config/services.yaml`.
