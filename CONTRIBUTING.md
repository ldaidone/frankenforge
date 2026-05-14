# Contributing to FrankenForge

Thanks for your interest in contributing to FrankenForge.

The project is still in early beta and evolving actively, so feedback, issues, discussions, and pull requests are all welcome.

FrankenForge is intentionally small, explicit, and close to raw PHP. Contributions should aim to preserve those characteristics.

---

# Philosophy

FrankenForge favors:

- explicit architecture
- minimal abstractions
- readable request flow
- stateless shared services
- worker-aware runtime design
- server-rendered applications
- low runtime overhead

The project is not trying to become a giant framework ecosystem.

When contributing, prefer:
- clarity over cleverness
- explicit behavior over hidden magic
- small focused changes over broad rewrites

---

# Development Setup

Clone the repository:

bash git clone <repo-url> cd frankenforge 

Prepare the environment:

bash cp .env.example .env touch storage/app.db 

Start the development environment:

bash make dev 

Run migrations and seed demo data:

bash make migrate_up make seed 

Open:

txt http://localhost 

Default login:

txt admin@frankenforge.dev changeme 

---

# Running Tests

Run the test suite:

bash make test 

---

# Project Structure

txt src/ ├── Core/       # Reusable runtime/kernel components ├── Domains/    # Application/domain logic └── Shared/     # Shared infrastructure and UI 

General responsibilities:

| Directory | Responsibility |
|---|---|
| Core/ | Runtime primitives and reusable engine components |
| Domains/ | Business/domain-specific application logic |
| Shared/ | Shared infrastructure and cross-domain helpers |

---

# Contribution Guidelines

## Good Contributions

Examples of welcome contributions:

- bug fixes
- tests
- documentation improvements
- performance improvements
- runtime safety improvements
- HTMX examples
- deployment examples
- worker lifecycle improvements
- developer tooling

---

## Please Avoid

FrankenForge intentionally avoids large framework-style abstractions.

Please avoid contributions that introduce:

- service locator patterns
- hidden runtime behavior
- excessive reflection
- deep inheritance chains
- unnecessary dependencies
- "magic" APIs
- global state
- ORM-style complexity

The goal is to keep the runtime understandable and explicit.

---

# Worker Runtime Considerations

FrankenForge runs on FrankenPHP worker mode.

This means services may remain resident in memory across requests.

When contributing:

- avoid request state leaking into shared services
- prefer stateless service design
- be careful with static state and singletons
- think about lifecycle and memory persistence

Fresh request/response objects are created per request, but shared services remain alive during the worker lifecycle.

---

# Pull Requests

Small and focused pull requests are preferred.

Please try to:

- keep PRs scoped to one concern
- include tests when possible
- explain architectural decisions clearly
- avoid mixing unrelated changes

Draft PRs are completely fine.

---

# Discussions & Feedback

Architecture discussions are encouraged, especially around:

- FrankenPHP worker lifecycle patterns
- persistent runtime safety
- HTMX workflows
- performance
- developer ergonomics
- DDD boundaries
- deployment strategies

---

# Code Style

General preferences:

- modern PHP 8.3+
- typed properties
- readonly where appropriate
- constructor injection
- explicit dependencies
- minimal side effects

Readability is preferred over clever abstractions.

---

# Reporting Issues

When opening issues, helpful information includes:

- PHP version
- FrankenPHP version
- reproduction steps
- logs/errors
- environment details
- expected vs actual behavior

Minimal reproducible examples are appreciated.

---

# Final Notes

FrankenForge is still evolving.

The project aims to stay:
- lightweight
- explicit
- worker-oriented
- easy to reason about

Thanks for helping improve it.
