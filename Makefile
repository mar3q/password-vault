
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
