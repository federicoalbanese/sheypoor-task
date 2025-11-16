DC=docker compose
DC_LOCAL=$(DC) -f docker-compose.yml -f docker-compose.local.yml
DC_PROD=$(DC) -f docker-compose.yml -f docker-compose.prod.yml
DC_STAGE=$(DC) -f docker-compose.yml -f docker-compose.stage.yml
DC_EXEC=$(DC) exec

dev: clean
	$(DC_LOCAL) up -d

prod: clean
	$(DC_PROD) up -d

stage: clean
	$(DC_STAGE) up -d

build_dev: clean
	$(DC_LOCAL) up -d --build

build_prod: clean
	$(DC_PROD) up -d --build

build_stage: clean
	$(DC_STAGE) up -d --build

down:
	$(DC) down

down_dev:
	$(DC_LOCAL) down

restart:
	$(DC) restart

clean:
	$(DC) down --remove-orphans

logs:
	$(DC) logs -f

shell-%:
	$(DC_EXEC) -it $* sh

root-shell-%:
	$(DC_EXEC) -it -u root $* sh

composer-install:
	$(DC_EXEC) app composer install

composer-install-prod:
	$(DC_EXEC) app composer install --no-dev

composer-update:
	$(DC_EXEC) app composer update

clear-cache:
	$(DC_EXEC) app php artisan cache:clear
	$(DC_EXEC) app php artisan config:clear
	$(DC_EXEC) app php artisan route:clear
	$(DC_EXEC) app php artisan view:clear
	$(DC_EXEC) app php artisan opcache:clear

worker-restart:
	$(DC_EXEC) worker supervisorctl restart all

worker-status:
	$(DC_EXEC) worker supervisorctl status

db-migrate:
	$(DC_EXEC) app php artisan migrate

db-rollback:
	$(DC_EXEC) app php artisan migrate:rollback

db-fresh:
	$(DC_EXEC) app php artisan migrate:fresh

test:
	$(DC_EXEC) app php artisan test

test-coverage:
	$(DC_EXEC) app php artisan test --coverage

.PHONY: dev prod stage build_dev build_prod build_stage down down_dev restart clean logs composer-install composer-install-prod composer-update clear-cache worker-restart worker-status db-migrate db-rollback db-fresh test test-coverage
