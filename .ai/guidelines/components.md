# External Components

Documents packages used in this project that do not ship their own Boost guidelines or skill.

---

## dedoc/scramble

Auto-generates an OpenAPI 3.1 spec and a browsable UI from routes, Eloquent API Resources, and PHPDoc. No separate spec file to maintain.

**How it's used:**
- Controllers use `#[Endpoint]`, `#[Group]`, and `#[Response]` attributes to enrich the generated docs.
- `@unauthenticated` PHPDoc tag marks public endpoints (e.g. login).
- The UI is available at `/docs/api` when the app is running.
- Config lives in `config/scramble.php`.

**When adding endpoints:** always add the `#[Endpoint]`, `#[Group]`, and `#[Response]` attributes to keep the docs accurate. Check sibling controllers for the existing patterns.
