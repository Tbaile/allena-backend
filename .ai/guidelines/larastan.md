# Larastan (PHPStan for Laravel)

- If you have modified any PHP files, you must run `docker compose exec php vendor/bin/phpstan analyse --memory-limit=256M` before finalizing changes to catch type errors and static analysis issues.
- Do not suppress PHPStan errors with `@phpstan-ignore` without a clear explanation of why the suppression is justified.
- The project runs at level 10 (maximum strictness). Ensure new code satisfies all type constraints.
