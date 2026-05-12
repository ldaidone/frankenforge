.PHONY: help dev stop test migrate_up migrate_down migrate_status seed build clean shell composer

CONTAINER_NAME := frankenforge

# ── Help ───────────────────────────────────────────────────────────
help:
	@echo ""
	@echo "  ███████╗██████╗  █████╗ ███╗   ██╗██╗  ██╗███████╗███╗   ██╗███████╗ ██████╗ ██████╗  ██████╗ ███████╗"
	@echo "  ██╔════╝██╔══██╗██╔══██╗████╗  ██║██║ ██╔╝██╔════╝████╗  ██║██╔════╝██╔═══██╗██╔══██╗██╔════╝ ██╔════╝"
	@echo "  █████╗  ██████╔╝███████║██╔██╗ ██║█████╔╝ █████╗  ██╔██╗ ██║█████╗  ██║   ██║██████╔╝██║  ███╗█████╗"
	@echo "  ██╔══╝  ██╔══██╗██╔══██║██║╚██╗██║██╔═██╗ ██╔══╝  ██║╚██╗██║██╔══╝  ██║   ██║██╔══██╗██║   ██║██╔══╝"
	@echo "  ██║     ██║  ██║██║  ██║██║ ╚████║██║  ██╗███████╗██║ ╚████║██║     ╚██████╔╝██║  ██║╚██████╔╝███████╗"
	@echo "  ╚═╝     ╚═╝  ╚═╝╚═╝  ╚═╝╚═╝  ╚═══╝╚═╝  ╚═╝╚══════╝╚═╝  ╚═══╝╚═╝      ╚═════╝ ╚═╝  ╚═╝ ╚═════╝ ╚══════╝"
	@echo ""
	@echo "  Available commands:"
	@echo ""
	@echo "    make dev				Start the development server"
	@echo "    make stop				Stop the development server"
	@echo "    make test				Run tests"
	@echo ""
	@echo "    make migrate_up			Run database migrations"
	@echo "    make migrate_down			Rollback last migration"
	@echo "    make migrate_status			Show migration status"
	@echo "    make seed				Seed database with demo data"
	@echo ""
	@echo "    make build				Optimize autoloader"
	@echo "    make clean				Clean cache files"
	@echo ""

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
migrate_up:
	@echo "🗄️ Running migrations..."
	php bin/migrate.php up

migrate_down:
	@echo "↩️ Rolling back last migration..."
	php bin/migrate.php down

migrate_status:
	@echo "📋 Migration status..."
	php bin/migrate.php status

seed:
	@echo "🌱 Seeding database..."
	php bin/seed.php all -f

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