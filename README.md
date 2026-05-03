# Fairly — Backend API

Laravel 13 REST API backend for the **Fairly** fitness planner. Consumed by native mobile apps (Android first).

## What is Fairly?

Fairly connects fitness **experts** with their **clients**. Experts build a personalised exercise programme for each client — picking exercises from a shared library, attaching a weekly schedule (days, sets, reps), and adding guidance notes. Clients view their daily programme and log each completed session. Experts track adherence over time.

## Tech Stack

| Concern | Choice |
|---------|--------|
| Framework | Laravel 13 / PHP 8.5 |
| Auth | Laravel Sanctum (token-based) |
| Roles & permissions | Spatie Laravel Permission |
| Response format | Eloquent API Resources |
| API docs | Scramble — auto-generates OpenAPI 3.1 spec from routes + resources |
| Database | PostgreSQL |
| Testing | Pest v4 (feature tests against real DB) |
| Static analysis | Larastan level 10 |
| Code style | Laravel Pint |

## Getting Started

### Prerequisites

Docker and Docker Compose must be installed. All PHP, Composer, and Node commands run inside containers — never directly on the host.

### Setup

```bash
# Start the stack
docker compose up -d

# Install dependencies, generate app key, run migrations, build assets
docker compose exec php composer setup
```

### Running the app

```bash
composer dev          # Starts server, queue, pail log tail, and Vite in parallel
```

### Running tests

```bash
docker compose exec php php artisan test --compact
```

### Code quality

```bash
# Fix code style
docker compose exec php vendor/bin/pint

# Static analysis
docker compose exec php vendor/bin/phpstan analyse --memory-limit=256M
```

## Project Specifications

Detailed feature specs live in [`docs/specs/`](docs/specs/). Start with the [overview](docs/specs/00-overview.md).

| # | Feature | Spec |
|---|---------|------|
| F1 | Authentication & Users | [docs/specs/F1-auth.md](docs/specs/F1-auth.md) |
| F2 | Exercise Library | [docs/specs/F2-exercises.md](docs/specs/F2-exercises.md) |
| F3 | Assignments | [docs/specs/F3-assignments.md](docs/specs/F3-assignments.md) |
| F4 | Exercise Logs | [docs/specs/F4-logs.md](docs/specs/F4-logs.md) |
| F5 | Expert–Client Relationships | [docs/specs/F5-relationships.md](docs/specs/F5-relationships.md) |
