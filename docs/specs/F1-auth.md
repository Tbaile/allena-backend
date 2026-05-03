# F1 — Authentication & Users

**Status:** planned

## Overview

Token-based authentication via Laravel Sanctum. Users authenticate with email and password and receive a personal access token for all subsequent API requests.

There is no public registration. Every user — admin, expert, or client — is created via an invite. The initial admin account is seeded from environment variables.

Two roles exist at launch: `expert` and `client`. A third role `admin` is seeded alongside the admin user. Roles are managed by Spatie Laravel Permission.

---

## User Creation Flow

```
env vars (ADMIN_EMAIL / ADMIN_PASSWORD)
  └── DatabaseSeeder creates the admin user

Admin
  └── POST /api/v1/users/invite  (role: expert)
        └── Expert account created, temporary password emailed

Expert
  └── POST /api/v1/clients/invite  (role: client)
        └── Client account created, temporary password emailed
```

---

## Endpoints

### Public (no token required)

| Method | URI | Description |
|--------|-----|-------------|
| `POST` | `/api/v1/auth/login` | Login and receive a Sanctum token |

### Authenticated

| Method | URI | Description |
|--------|-----|-------------|
| `POST` | `/api/v1/auth/logout` | Revoke current token |
| `GET` | `/api/v1/me` | Get authenticated user's profile + role |
| `PUT` | `/api/v1/me` | Update own profile (name, password) |

### Admin-only

| Method | URI | Description |
|--------|-----|-------------|
| `POST` | `/api/v1/users/invite` | Invite a new expert by email |

### Expert-only

| Method | URI | Description |
|--------|-----|-------------|
| `POST` | `/api/v1/clients/invite` | Invite a new client by email |

---

## Request / Response Shapes

### `POST /api/v1/auth/login`

**Request**
```json
{
  "email": "jane@example.com",
  "password": "secret"
}
```

**Response `200`**
```json
{
  "token": "1|abc123…",
  "user": {
    "id": 1,
    "name": "Jane Doe",
    "email": "jane@example.com",
    "role": "client"
  }
}
```

### `POST /api/v1/users/invite` (admin) / `POST /api/v1/clients/invite` (expert)

**Request**
```json
{
  "name": "John Smith",
  "email": "john@example.com"
}
```

**Response `201`**
```json
{
  "id": 7,
  "name": "John Smith",
  "email": "john@example.com",
  "role": "expert"
}
```

The invited user receives an email with a temporary password.

### `GET /api/v1/me`

**Response `200`**
```json
{
  "id": 1,
  "name": "Jane Doe",
  "email": "jane@example.com",
  "role": "client",
  "must_change_password": true,
  "created_at": "2026-01-01T00:00:00Z"
}
```

---

## Admin Seeder

The first admin user is created by `DatabaseSeeder` using env variables:

```
ADMIN_NAME=Admin
ADMIN_EMAIL=admin@fairly.app
ADMIN_PASSWORD=changeme
```

The seeder is idempotent — running it twice does not create a duplicate.

---

## Business Rules

- There is no public registration endpoint.
- Experts are invited by the admin only.
- Clients are invited by their assigned expert only.
- An invited user receives a temporary password by email and is flagged `must_change_password = true`.
- The user must update their password on first login before accessing other endpoints.
- A token has no explicit expiry unless manually revoked.
- Passwords must be at least 8 characters.

---

## Open Questions

- [ ] Should tokens have an expiry (e.g. 90 days)?
- [ ] Do we need multi-device support (multiple active tokens per user)?
- [ ] Should the `must_change_password` enforcement be a middleware gate or just a flag the mobile app reads?
