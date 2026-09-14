SHELL := /bin/bash
.DEFAULT_GOAL := help

-include .env
export

DC  := docker compose
PHP := $(DC) exec -u www-data php
YII := $(PHP) php yii

.PHONY: help urls init up down restart destroy build rebuild ps logs sh composer-install composer-require migrate migrate-down migrate-create cs cs-fix stan check mysql-cli redis-cli

help:
	@grep -hE '^[a-zA-Z0-9_.-]+:.*## ' $(MAKEFILE_LIST) \
		| awk 'BEGIN {FS = ":.*## "}; {printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2}'

.env:
	@cp .env.example .env
	@echo "Создан .env из .env.example"

init: ## Первый запуск проекта с нуля
	$(MAKE) build
	$(MAKE) up
	$(MAKE) composer-install
	$(MAKE) migrate
	@$(MAKE) --no-print-directory urls

urls: ## Адреса сервисов
	@echo "Приложение:     http://localhost:$(NGINX_PORT)"
	@echo "RabbitMQ UI:    http://localhost:$(RABBITMQ_MANAGEMENT_PORT)"
	@echo "Mailpit:        http://localhost:$(MAILPIT_UI_PORT)"
	@echo "Elasticsearch:  http://localhost:$(ES_HOST_PORT)"
	@echo "Kibana:         http://localhost:$(KIBANA_PORT)  (make up-tools)"

up: ## Поднять окружение
	$(DC) up -d --wait

up-tools: ## Поднять окружение вместе с Kibana
	$(DC) --profile tools up -d --wait

down: ## Остановить контейнеры, данные в томах сохранить
	$(DC) down

restart: ## Перезапустить контейнеры
	$(DC) restart

destroy: ## Удалить контейнеры
	@read -p "Удалить все данные — БД, индекс, очереди? [y/N] " ok; \
	[ "$$ok" = "y" ] || { echo "Отменено"; exit 1; }; \
	$(DC) down -v --remove-orphans

build: ## Собрать образы
	$(DC) build

rebuild: ## Пересобрать образы без кэша
	$(DC) build --no-cache

ps: ## Статус сервисов
	$(DC) ps

logs: ## Логи (make logs s=php)
	$(DC) logs -f --tail=100 $(s)

sh: ## Shell внутри php-контейнера
	$(PHP) bash

composer-install: ## Установить PHP-зависимости
	$(PHP) composer install --no-interaction --prefer-dist

composer-require: ## Добавить пакет (make composer-require p=php-amqplib/php-amqplib)
	@test -n "$(p)" || { echo "Укажите p=vendor/package"; exit 1; }
	$(PHP) composer require $(p)

migrate: ## Накатить миграции
	$(YII) migrate --interactive=0

migrate-down: ## Откатить N последних миграций (make migrate-down n=1)
	$(YII) migrate/down $(or $(n),1)

migrate-create: ## Создать миграцию (make migrate-create name=create_ads_table)
	@test -n "$(name)" || { echo "Укажите name=..."; exit 1; }
	$(YII) migrate/create $(name) --interactive=0

cs: ## Проверить стиль кода
	$(PHP) vendor/bin/phpcs

cs-fix: ## Починить стиль кода
	$(PHP) vendor/bin/phpcbf

stan: ## Статический анализ
	$(PHP) vendor/bin/phpstan --memory-limit=-1

check: ## Стиль, статанализ и тесты подряд
	$(MAKE) cs
	$(MAKE) stan
	$(MAKE) test

mysql-cli: ## Консоль MySQL
	$(DC) exec mysql mysql -u$(DB_USER) -p$(DB_PASSWORD) $(DB_NAME)

redis-cli: ## Консоль Redis
	$(DC) exec redis redis-cli

rbac: ## Таблицы RBAC, роли и права
	$(YII) migrate --migrationPath=@yii/rbac/migrations --interactive=0
	$(YII) rbac/init
