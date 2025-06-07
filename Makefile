APP_SH=docker compose exec -u www-data app sh -c
RABBITMQ_SH=docker compose exec rabbitmq sh -c

DATABASE_SERVICE=database

include .env

help:
	@grep -E '^[a-zA-Z\._-]+:.*?## .*$$' Makefile | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[1;32m%-25s\033[0m %s\n", $$1, $$2}'

up: ## Start containers
	docker compose up --build -d --remove-orphans

down: ## Stop containers
	docker compose down

.SILENT: rabbitmq.prepare
rabbitmq.prepare: ## RabbitMQ: Add publisher and consumer users
	$(RABBITMQ_SH) "rabbitmqctl add_user ${RABBITMQ_PUBLISHER_USERNAME} ${RABBITMQ_PUBLISHER_PASSWORD}" || true
	$(RABBITMQ_SH) "rabbitmqctl set_permissions -p / ${RABBITMQ_PUBLISHER_USERNAME} '^${RABBITMQ_EXCHANGE}$$' '^${RABBITMQ_EXCHANGE}$$' ''" || true
	$(RABBITMQ_SH) "rabbitmqctl add_user ${RABBITMQ_CONSUMER_USERNAME} ${RABBITMQ_CONSUMER_PASSWORD}" || true
	$(RABBITMQ_SH) "rabbitmqctl set_permissions -p / ${RABBITMQ_CONSUMER_USERNAME} '^${RABBITMQ_EXCHANGE}.*$$' '^${RABBITMQ_EXCHANGE}.*$$' '.*'" || true

app.artisan: ## App: Run Laravel `artisan` command (example: `make app.artisan command="migrate"`)
	$(APP_SH) "./artisan $(command)"

app.clear-cache: ## App: Clear cache
	make app.artisan command="optimize:clear"

app.migrate: ## App: Run migrations and seed database
	$(APP_SH) './artisan migrate --seed'

app.generate-php-doc: ## Generate PHPDoc using IDE Helper Generator (example: `make app.generate-php-doc` for everything or `make app.generate-php-doc: model="\Some\Namespace\Model"` for specific model)
ifndef model
	$(APP_SH) './artisan ide-helper:generate && ./artisan ide-helper:models -W -N'
endif
ifdef model
	$(APP_SH) "./artisan ide-helper:models -W '$(model)'"
endif

app.migrate-rollback-last: ## App: rollback last migration
	$(APP_SH) './artisan migrate:rollback --step=1'

app.composer: ## App: Run `composer` command (example: `make app.composer command="require --prefer-dist vendor/package"`)
	$(APP_SH) "composer $(command)"

app.analyze: ## App: Run static analysis tool
	$(APP_SH) "./vendor/bin/phpstan clear-result-cache"
	$(APP_SH) "./vendor/bin/phpstan analyse --ansi"

app.code-style-test: ## App: Test code style
	$(APP_SH) './vendor/bin/pint --test --config pint.json'

app.code-style-repair: ## App: Automatically repair code style
	$(APP_SH) './vendor/bin/pint --repair --config pint.json'

app.test: ## App: Run all tests (or single test `make test path="tests/Unit/Modules/Databank/Import/ImporterTest.php"`)
ifndef path
	$(APP_SH) "./artisan test"
endif
ifdef path
	$(APP_SH) "./artisan test --stop-on-failure '$(path)'"
endif

app.check-all: ## App: Check all (run static analysis tool, test code style and run tests)
	make app.code-style-test && make app.analyze && make app.test

db.import: ## Import database dump from given file
ifndef path
	$(error `path` not specified)
endif
ifdef clean
	docker compose exec -T ${DATABASE_SERVICE} psql --username ${POSTGRES_USER} ${POSTGRES_DATABASE} -c "drop schema public cascade; create schema public;"
endif
ifdef path
	docker compose exec -T ${DATABASE_SERVICE} psql --username ${POSTGRES_USER} ${POSTGRES_DATABASE} < "$(path)"
endif

db.dump: ## Create database dump to given directory
ifndef path
	$(error `path` not specified)
endif
ifdef path
	docker compose exec -T ${DATABASE_SERVICE} pg_dump --username ${POSTGRES_USER} ${POSTGRES_DATABASE} > $(path)/${POSTGRES_DATABASE}.sql
endif