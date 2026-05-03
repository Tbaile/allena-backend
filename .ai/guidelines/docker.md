# Docker Environment

**ALL** commands that interact with PHP, Node, or the database MUST be run inside the appropriate Docker container. NEVER run `php`, `composer`, `node`, `npm`, `npx`, `pest`, `pint`, `phpstan`, or database clients directly on the host machine — no exceptions.

Use `docker compose exec` to run commands inside the relevant service:

- **PHP / Artisan / Composer**: `docker compose exec php php artisan ...` or `docker compose exec php composer ...`
- **Pint**: `docker compose exec php vendor/bin/pint ...`
- **PHPStan / Larastan**: `docker compose exec php vendor/bin/phpstan ...`
- **Pest / Tests**: `docker compose exec php php artisan test ...`
- **Node / NPM**: `docker compose exec node npm ...`
- **Database (psql/sqlite)**: `docker compose exec [db-service] ...`

If a `docker compose exec` call fails because the container is not running, start the stack first with `docker compose up -d`, then retry the exact same command. Do not fall back to running the command directly on the host.
