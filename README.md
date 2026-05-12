<div align="center">

<img src="assets/logo.jpg" alt="FrankenForge" width="400" style="margin-top: 50px; border-radius: 5pc; filter: drop-shadow(0px 0px 15px rgba(255,119,0,0.68));" />

# FrankenForge

**Forge the Monster. Master the Architecture.**

[![PHP](https://img.shields.io/badge/PHP-8.3+-%23777BB4?logo=php)](https://php.net)
[![FrankenPHP](https://img.shields.io/badge/FrankenPHP-worker-%234D4D4D?logo=data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IiNmZmYiIHN0cm9rZS13aWR0aD0iMiI+PHBvbHlsaW5lIHBvaW50cz0iMTIgMiAyMiA3IDIyIDE3IDEyIDIyIDIgMTcgMiA3IDEyIDIiLz48cG9seWxpbmUgcG9pbnRzPSIyIDcgMTIgMTIgMjIgNyIvPjxwb2x5bGluZSBwb2ludHM9IjEyIDEyIDEyIDIyIi8+PC9zdmc+)](https://frankenphp.dev)
[![HTMX](https://img.shields.io/badge/HTMX-2-%233366CC)](https://htmx.org)
[![Tailwind](https://img.shields.io/badge/Tailwind-4-%2306B6D4?logo=tailwindcss)](https://tailwindcss.com)
[![SQLite](https://img.shields.io/badge/SQLite-WAL-%23003B57?logo=sqlite)](https://sqlite.org)
[![License](https://img.shields.io/badge/License-Apache%202.0-%23A31F34)](LICENSE)

---

The zero-bloat, framework-less PHP 8.3+ kernel built on **FrankenPHP** & **HTMX**.  
Full DDD/Hexagonal control for staff-level engineers.  
Zero Laravel. Zero Symfony. Zero tax.

---

[Quick Start](#quick-start) · [What Is FrankenForge](#what-is-frankenforge) · [Architecture](#architecture) · [Showcase](#showcase) · [Routes](#routes) · [Commands](#commands) · [Configuration](#configuration) · [Support](#support)

</div>

---

## What Is FrankenForge

FrankenForge is a **thin execution engine** for PHP — not a framework. It is designed for experienced developers who want full architectural control without the overhead, magic, or opinionated defaults of traditional frameworks.

The kernel provides the essential scaffolding: a DI container, HTTP abstraction, router, templating engine, CSRF protection, validation, and logging. Everything is explicit, readable, and easy to reason about.

**The goal:** give you enough infrastructure to build production applications, without getting in your way.

### FrankenForge Is

- ✅ A **FrankenPHP worker-mode kernel** — persistent in-memory state across requests, no per-request bootstrapping
- ✅ An **HTMX-first rendering system** — server-rendered HTML fragments, content negotiation for JSON
- ✅ A **DDD-aligned project skeleton** — domain logic stays pure and isolated from infrastructure
- ✅ A **set of reusable primitives** — container, router, HTTP, view engine, validation, security
- ✅ A **batteries-included showcase** — the Admin and Dashboard domains demonstrate real patterns

### FrankenForge Is NOT

- ❌ A Laravel / Symfony replacement
- ❌ A React / Vue / SPA framework
- ❌ A Node.js-dependent toolchain
- ❌ An ORM or query builder
- ❌ Opinionated about your domain structure

---

## Quick Start

```bash
# 1. Clone and prepare environment
cp .env.example .env
touch storage/app.db          # Create empty SQLite database

# 2. Start the FrankenPHP container
make dev

# 3. Run migrations and seed demo data
make migrate_up
make seed

# 4. Open in browser
open http://localhost

# Login: admin@frankenforge.dev / changeme
```

> **No Node.js required.** Tailwind is loaded via CDN. No build step. No JS runtime.

**Prerequisites:** Docker and Docker Compose installed on your machine.

---

## Architecture

The project has two distinct layers — the **kernel** (reusable) and the **showcase domains** (example usage).

### The Kernel (`src/Core/`)

The kernel is the reusable engine. It provides:

| Component | Purpose |
|---|---|
| `Container` | Lightweight DI container — `set()`, `factory()`, `get()`, `make()`, `has()` |
| `Router` | FastRoute wrapper with middleware onion pipeline |
| `Request` | Superglobals wrapper — headers, query, body, JSON, files |
| `Response` | Status code, headers, body — fluent API, `send()` guard |
| `View` | Native PHP output-buffering template engine |
| `Responder` | Content negotiator — HTML vs HTMX fragment vs JSON |
| `Validator` | Array-based validation — required, email, integer, min, max, in, regex, etc. |
| `CsrfToken` | Session-bound CSRF token generation and validation |
| `FlashMessages` | Session-based flash messages (consume-on-read) |
| `FileLogger` | JSON-line file logger with level filtering |
| `ErrorHandler` | 404 / 405 / 500 error page renderer |
| `JsonResponder` | JSON API response helpers — data, error, paginated, created, noContent |

You can reuse `src/Core/` in any FrankenPHP project. Replace the Domains, migrations, and templates with your own.

### Execution Model

```
Worker starts → Container built once → Loop: handle() → refresh session → dispatch route
                                                                    ↑ Container, DB, config are RESIDENT
                                                                    ↓ Request, Response are FRESH per cycle
```

```php
// public/index.php — the worker loop
while (frankenphp_handle_request(function () use ($container): void {
    session_start();
    $container->get('router')->dispatch();
})) {
    // Everything above the loop runs once
}
```

### Key Design Rules

- **Domain never depends on Infrastructure** — repositories are injected by interface
- **Controllers (Actions) are thin** — they parse input, call a service, return a response
- **Entities are immutable** — `readonly` classes, no setters
- **Value objects** enforce type safety (`Money` in cents, `Percentage` in basis points)
- **Services are stateless singletons** — shared across requests, must not leak request data
- **No service locator or global container** — dependency injection is explicit constructor wiring

---

## Showcase

The `src/Domains/` directory contains two **showcase domains** that demonstrate real-world patterns built on top of the kernel. These are not part of the kernel itself — they serve as a reference implementation and can be replaced with your own domains.

### Admin Domain (`src/Domains/Admin/`)

A full-featured admin panel with session-based authentication and system management tools:

| Feature | Description |
|---|---|
| **Auth** | Session-based login, Argon2id password hashing, force-password-change on first login |
| **System Overview** | PHP version, server info, environment, quick links |
| **Profile** | Update name/email, change password |
| **Env Viewer / Editor** | Browse and edit `.env` variables via the UI |
| **Database Browser** | List tables, view row data with pagination |
| **Migration Runner** | View status, run pending migrations, rollback (specific or cascade) |
| **Log Viewer** | Browse log file with level filtering (debug/info/warning/error) and pagination |

### Dashboard Domain (`src/Domains/Dashboard/`)

A live dashboard with HTMX-powered components and a public demo page:

| Feature | Description |
|---|---|
| **Stat Cards** | Live metrics with auto-refresh via HTMX polling (30s) |
| **Users Table** | Paginated user list with role badges |
| **Invoices Table** | Paginated invoice list with status badges and formatted amounts |
| **Feature Toggles** | Toggle UI with HTMX POST — no page reload |
| **Flash Messages** | Success / error / info / warning demos |
| **SSE Ping** | Real-time server heartbeat via `EventSource` |

### Domain Entities

| Entity | Domain | Key Properties |
|---|---|---|
| `AdminUser` | Admin | `id`, `name`, `email`, `passwordHash`, `mustChangePassword`, `createdAt` |
| `User` | Dashboard | `id`, `name`, `email`, `role`, `createdAt`, `lastLoginAt` |
| `Stat` | Dashboard | `key`, `label`, `value`, `icon`, `trend`, `up` |
| `Invoice` | Dashboard | `id`, `customerName`, `amountCents`, `currency`, `issuedAt`, `status` |

### Value Objects

| Value Object | Domain | Purpose |
|---|---|---|
| `Money` | Dashboard | Cent-based monetary amounts with currency |
| `Percentage` | Dashboard | Basis-point-based percentages (0-10000) |

---

## Routes

### Public

| Method | Path | Handler |
|---|---|---|
| GET | `/` | Landing page |
| GET | `/demo` | Demo dashboard |
| GET | `/demo/toggles` | Toggle listing (HTMX) |
| POST | `/dashboard/toggle/{feature}` | Toggle feature |

### API (public)

| Method | Path | Response |
|---|---|---|
| GET | `/api/stats` | JSON dashboard stats |
| GET | `/api/toggles` | JSON feature toggles |
| POST | `/api/toggles/{id}/toggle` | Toggle state |
| GET | `/api/counter` | Counter value |
| POST | `/api/counter/increment` | Increment counter |
| POST | `/api/counter/reset` | Reset counter |
| GET | `/api/ping` | SSE heartbeat stream |

### Admin Auth (public)

| Method | Path | Description |
|---|---|---|
| GET | `/dashboard/login` | Login form |
| POST | `/dashboard/login` | Submit login |
| GET | `/dashboard/logout` | Logout |

### Admin (authenticated)

| Method | Path | Description |
|---|---|---|
| GET | `/dashboard/overview` | System overview |
| GET | `/dashboard/profile` | Profile page |
| POST | `/dashboard/profile` | Update profile |
| GET | `/dashboard/password` | Change password form |
| POST | `/dashboard/password` | Change password |
| GET | `/dashboard/env` | Environment viewer |
| POST | `/dashboard/env/save` | Save .env file |
| GET | `/dashboard/database` | Database table list |
| GET | `/dashboard/database/{table}` | Table data viewer |
| GET | `/dashboard/migrations` | Migration status |
| POST | `/dashboard/migrations/run` | Run / rollback migrations |
| GET | `/dashboard/logs` | Log viewer |

### HTMX Fragments (authenticated)

| Method | Path | Content |
|---|---|---|
| GET | `/dashboard/stats` | Stat cards |
| GET | `/dashboard/users` | Users table |
| GET | `/dashboard/invoices` | Invoices table |
| POST | `/flash/{type}` | Flash message demo |

---

## Commands

```bash
make dev              # Start FrankenPHP (Docker)
make stop             # Stop FrankenPHP
make shell            # Open shell in container
make test             # Run PHPUnit tests
make migrate_up       # Run pending migrations
make migrate_down     # Rollback last migration
make migrate_status   # Show migration status
make seed             # Seed demo data
make build            # Optimize autoloader
make clean            # Remove cache files
```

### Manual CLI

```bash
php bin/migrate.php up [N]       # Run [N] pending migrations
php bin/migrate.php down [N]     # Rollback [N] migrations
php bin/migrate.php status       # Show migration status
php bin/seed.php all -f          # Force re-seed all tables
```

### Seeded Data

Running `make seed` populates the database with:

- **5 users** including `admin@frankenforge.dev` (password: `changeme`)
- **4 stats** — revenue, users, orders, growth
- **3 invoices** — paid, pending, overdue statuses
- **3 toggles** — dark mode, beta features, notifications

---

## Configuration

### Environment (`.env`)

```env
APP_ENV=local          # local | production
APP_DEBUG=true         # Show full error details (set false in production)
DATABASE_URL=sqlite:/app/storage/app.db
```

### FrankenPHP (`Caddyfile`)

```caddyfile
{
    frankenphp {
        worker /app/public/index.php
    }
}

http://localhost, https://localhost {
    root * /app/public
    php_server
    encode gzip
}
```

- Worker mode: single persistent PHP process
- Serves from `/app/public`
- Gzip compression enabled
- Listens on HTTP and HTTPS

### Theme System

The dashboard supports dark and light themes:

- Toggle persisted in `localStorage` under `frankenforge-theme`
- Tailwind CSS loaded via CDN (configurable in `templates/theme-config.html.php`)
- Fonts: **Inter** (sans-serif) for UI, **JetBrains Mono** (monospace) for code
- CSS custom properties for brand colors, backgrounds, and text

---

## Project Structure

```
├── AGENTS.md              # AI agent operating instructions
├── Caddyfile              # FrankenPHP server config
├── Makefile               # Development command targets
├── docker-compose.yml     # Docker Compose setup
├── composer.json           # PHP dependencies
│
├── bin/                   # CLI scripts
│   ├── migrate.php        # Migration runner
│   └── seed.php           # Database seeder
│
├── config/
│   └── services.php       # DI wiring + route definitions
│
├── migrations/            # Database migration files
│
├── public/
│   ├── index.php          # Front controller / worker loop
│   └── assets/            # Static assets
│
├── src/
│   ├── Core/              # Kernel — reusable engine
│   │   ├── Container/
│   │   ├── Http/
│   │   ├── Router/
│   │   ├── View/
│   │   ├── Validation/
│   │   ├── Security/
│   │   ├── Session/
│   │   ├── Logging/
│   │   ├── Error/
│   │   └── Responders/
│   │
│   ├── Domains/           # Showcase — replace with your domains
│   │   ├── Admin/         # Admin panel domain
│   │   │   ├── Actions/   #   Invokable controllers
│   │   │   ├── Entities/  #   Domain entities
│   │   │   ├── Http/      #   Middleware
│   │   │   ├── Services/  #   Business logic
│   │   │   └── Views/     #   Templates
│   │   │
│   │   └── Dashboard/     # Dashboard domain
│   │       ├── Actions/
│   │       ├── Entities/
│   │       ├── Repositories/  # Interfaces
│   │       ├── ValueObjects/  # Value objects
│   │       └── Views/
│   │
│   └── Shared/            # Shared infrastructure
│       ├── Infrastructure/
│       │   └── Database/  # PDO, Migrator, SQLite repos
│       └── UI/            # Shared components (sidebar)
│
├── storage/               # SQLite database + log files
├── templates/             # Layout shells + theme config
└── tests/                 # PHPUnit tests
```

---

## Testing

```bash
make test
```

Tests cover the core kernel components:

- **Router** — route matching, parameter extraction, 404, 405
- **Validator** — all rule types (required, email, integer, min, max, in, regex, matches, url, uuid), custom messages, closure rules, optional fields

---

## Stack

| Component | Choice | Why |
|---|---|---|
| Runtime | **FrankenPHP** (worker mode) | Persistent in-memory state, no per-request bootstrapping |
| Language | **PHP 8.3+** | Typed properties, readonly classes, enums, named arguments |
| Routing | **nikic/fast-route** | Fast, simple, no magic — just route matching |
| Templating | **Native PHP** (output buffering) | No learning curve, no compilation step, maximum performance |
| Frontend | **HTMX 2** | Server-rendered HTML, hypermedia-driven interactivity |
| Styling | **Tailwind CSS 4** (CDN) | Utility-first, no build step, no Node.js |
| Database | **SQLite** via PDO | Zero-config, WAL mode for concurrency, file-based |
| Auth | **Session-based** + Argon2id | Simple, secure, no external dependencies |
| Testing | **PHPUnit 13** | Standard PHP testing framework |

---

## Support

If you find FrankenForge useful, consider supporting the project:

[!["Buy Me A Coffee"](https://www.buymeacoffee.com/assets/img/custom_images/orange_img.png)](https://www.buymeacoffee.com/leodaido)

---

<div align="center">

**Built for engineers who outgrew frameworks.**

</div>
