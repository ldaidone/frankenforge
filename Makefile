.PHONY: dev stop test migrate build clean shell composer

CONTAINER_NAME := frankenforge

# ── Development ──────────────────────────────────────────────
dev:
	@echo "🔥 Starting FrankenForge..."
	docker compose up -d

stop:
	@echo "🛑 Stopping FrankenForge..."
	docker compose down

shell:
	docker exec -it $(CONTAINER_NAME) sh

# ── Testing ──────────────────────────────────────────────────
test:
	vendor/bin/phpunit --configuration phpunit.xml

# ── Database ─────────────────────────────────────────────────
migrate:
	@echo "🗄️ Running migrations..."
	docker exec $(CONTAINER_NAME) php /app/public/index.php migrate

# ── Build ────────────────────────────────────────────────────
build:
	@echo "📦 Optimizing autoloader..."
	composer dump-autoload --optimize

# ── Composer ─────────────────────────────────────────────────
composer:
	docker exec $(CONTAINER_NAME) composer $(filter-out $@,$(MAKECMDGOALS))

# ── Cleanup ──────────────────────────────────────────────────
clean:
	@echo "🧹 Cleaning up..."
	find . -name '*.cache' -delete
	find . -name '.phpunit.result.cache' -delete
