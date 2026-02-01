
.PHONY: start down

start:
	docker compose up -d

down:
	docker compose down --remove-orphans

restart:
	@$(MAKE) down
	@$(MAKE) start

sh:
	docker compose exec php sh

composer-install:
	docker compose exec php composer install

phpstan:
	docker compose exec php vendor/bin/phpstan

phpcs-check:
	docker compose exec php vendor/bin/php-cs-fixer check

phpcs-fix:
	docker compose exec php vendor/bin/php-cs-fixer fix
