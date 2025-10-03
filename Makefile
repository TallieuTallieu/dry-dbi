-include .env
export

Red=\033[0;31m
Green=\033[0;32m
Yellow=\033[0;33m
Blue=\033[0;34m
Purple=\033[0;35m
Cyan=\033[0;36m
Orange=\033[0;33m
NC=\033[0m

## HELP ##
help:
	@echo "$(Cyan)Available commands:$(NC)"
	@echo ""
	@echo "$(Yellow)Docker:$(NC)"
	@echo "  docker-init    - Initialize Docker if not running"
	@echo "  docker         - Start Docker containers"
	@echo "  docker-exec    - Execute bash in dry-dbi-dev container"
	@echo ""
	@echo "$(Yellow)Yarn:$(NC)"
	@echo "  yarn-format    - Format all files with Prettier"
	@echo "  yarn-install   - Install yarn dependencies"
	@echo ""
	@echo "$(Yellow)Testing:$(NC)"
	@echo "  test           - Run all tests"
	@echo "  test-verbose   - Run tests with verbose output"
	@echo "  test-coverage  - Run tests with coverage report"
	@echo ""
	@echo "$(Yellow)Code Quality:$(NC)"
	@echo "  phpstan        - Run PHPStan static analysis"
	@echo "  phpstan-baseline - Generate PHPStan baseline"
	@echo ""
	@echo "  help           - Show this help message"
.PHONY: help

## DOCKER ##
docker-init:
	@if ! docker info >/dev/null 2>&1; then \
		echo "Docker is not running, starting Docker..."; \
		open -a Docker; \
		while ! docker info >/dev/null 2>&1; do \
			echo "Waiting for Docker to start..."; \
			sleep 5; \
		done; \
		echo "Docker is now running."; \
	else \
		echo "Docker is already running."; \
	fi
.PHONY: docker-init

docker: docker-init
	@if [ -z "$$(docker compose ps -q dry-dbi-dev)" ]; then \
		docker compose up -d --build; \
		else \
		echo "dry-dbi-dev is running."; \
		fi
.PHONY: docker

docker-exec: docker
	docker compose exec dry-dbi-dev bash
.PHONY: docker-exec

## YARN ##

yarn-format: docker
	docker compose exec -T dry-dbi-dev yarn format-js
.PHONY: yarn-format

yarn-install: docker
	docker compose exec -T dry-dbi-dev yarn
.PHONY: yarn-install

## TESTING ##

test: docker
	docker compose exec -T dry-dbi-dev ./vendor/bin/pest
.PHONY: test

test-verbose: docker
	docker compose exec -T dry-dbi-dev ./vendor/bin/pest -v
.PHONY: test-verbose

test-coverage: docker
	docker compose exec -T dry-dbi-dev ./vendor/bin/pest --coverage
.PHONY: test-coverage

## CODE QUALITY ##

phpstan: docker
	docker compose exec -T dry-dbi-dev composer phpstan
.PHONY: phpstan

phpstan-baseline: docker
	docker compose exec -T dry-dbi-dev composer phpstan:baseline
.PHONY: phpstan-baseline

