# Test-Driven Development

This project follows strict TDD. Write the failing test first, then implement the minimum code to make it pass.

- Write a failing Pest test before writing any implementation code.
- Run `docker compose exec php php artisan test --compact` to verify the test fails, then passes after implementation.
- Feature tests are preferred over unit tests. Test behaviour through HTTP, not internals.
- Do not write implementation code that is not covered by a test.
- Do not delete or skip tests to make a build pass.
