UID := $(shell id -u)
GID := $(shell id -g)

DOCKER_COMPOSE := UID=$(UID) GID=$(GID) docker compose
EXEC := $(DOCKER_COMPOSE) exec php
CONSOLE := $(EXEC) bin/console

.PHONY: docker-up docker-down docker-restart docker-build docker-sh \
        app-install \
        db-create db-migrate db-diff db-status db-validate \
        jwt-keys \
        qa qa-stan qa-cs qa-cs-fix qa-test \
        start stop restart sh

## Docker

docker-up:
	$(DOCKER_COMPOSE) up -d

docker-down:
	$(DOCKER_COMPOSE) down --remove-orphans

docker-restart: docker-down docker-up

docker-build:
	$(DOCKER_COMPOSE) build

docker-sh:
	$(EXEC) sh

## Dependencies

app-install:
	$(EXEC) composer install

## Database

db-create:
	$(CONSOLE) doctrine:database:create --if-not-exists
	$(CONSOLE) doctrine:database:create --if-not-exists --env=test

db-migrate: db-create
	$(CONSOLE) doctrine:migrations:migrate --no-interaction

db-diff:
	$(CONSOLE) doctrine:migrations:diff

db-status:
	$(CONSOLE) doctrine:migrations:status

db-validate:
	$(CONSOLE) doctrine:schema:validate

## JWT

jwt-keys:
	$(CONSOLE) lexik:jwt:generate-keypair --overwrite

## Quality

qa: qa-stan qa-cs qa-test

qa-stan:
	$(EXEC) vendor/bin/phpstan --memory-limit=512M

qa-cs:
	$(EXEC) vendor/bin/php-cs-fixer check

qa-cs-fix:
	$(EXEC) vendor/bin/php-cs-fixer fix

qa-test:
	$(EXEC) bin/phpunit --testdox

## Aliases

start: docker-up
stop: docker-down
restart: docker-restart
sh: docker-sh
