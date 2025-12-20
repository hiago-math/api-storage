include .env.example

setup:
	@export APP_URL=${APP_URL}; \
	@export APP_NAME=${APP_NAME}; \
	export NGINX_HOST_HTTP_PORT=${NGINX_HOST_HTTP_PORT}; \

start:
	@echo "Iniciando projeto..."
	@echo "Copiando .env.example do projeto..."
	@cp .env.example .env
	@echo "Iniciando container..."
	@docker compose up -d --build
	@echo "Instalando composer..."
	@docker compose exec app composer install
	@echo "Gerando chave do projeto..."
	@docker compose exec app php artisan key:generate
	@echo "Comando 'make start' executado com sucesso."
	@echo "URL API ${APP_URL}:${NGINX_HOST_HTTP_PORT}/api"
	@echo "URL DOCUMENTACAO ${APP_URL}:${NGINX_HOST_HTTP_PORT}/api/documentation"
	@echo $(shell date +"%Y-%m-%d %H:%M:%S") > storage/app/uptime.txt

shell-app:
	@docker compose exec app bash

shell-queue:
	@docker compose exec queue-work bash

shell-schedule:
	@docker compose exec schedule-runner bash

stop:
	@docker compose down
