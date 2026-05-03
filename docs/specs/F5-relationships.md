# F5 — Expert–Client Relationships

**Status:** planned

## Overview

An expert can manage multiple clients. A client can have at most one expert at a time (one-to-many). The relationship is established when an expert invites a client (F1) or manually links an existing client to themselves.

---

## Entity

The relationship is modelled as a pivot:

### expert_client (pivot table)

| Field | Type | Notes |
|-------|------|-------|
| `expert_id` | FK → User | |
| `client_id` | FK → User (unique) | A client belongs to one expert |
| `created_at` | timestamp | When the relationship was established |

---

## Endpoints

### Expert

| Method | URI | Description |
|--------|-----|-------------|
| `GET` | `/api/v1/clients` | List my clients |
| `GET` | `/api/v1/clients/{id}` | Get a client's profile + adherence summary |
| `POST` | `/api/v1/clients/{id}/link` | Link an existing user as my client |
| `DELETE` | `/api/v1/clients/{id}/link` | Unlink a client (does not delete the user) |

### Client

| Method | URI | Description |
|--------|-----|-------------|
| `GET` | `/api/v1/me/expert` | Get my assigned expert's profile |

---

## Response Shape — Client Profile (expert view)

```json
{
  "id": 7,
  "name": "Jane Doe",
  "email": "jane@example.com",
  "linked_since": "2026-01-10T09:00:00Z",
  "adherence": {
    "this_week": {
      "scheduled": 6,
      "logged": 4,
      "percentage": 67
    }
  },
  "active_assignments_count": 3
}
```

---

## Response Shape — `GET /clients`

```json
{
  "data": [
    {
      "id": 7,
      "name": "Jane Doe",
      "email": "jane@example.com",
      "active_assignments_count": 3,
      "last_log_at": "2026-02-03T18:30:00Z"
    }
  ],
  "meta": { "total": 12, "per_page": 20, "current_page": 1 }
}
```

---

## Business Rules

- A client can only be linked to one expert at a time. Linking to a new expert automatically removes the previous link.
- Unlinking a client does **not** deactivate their existing assignments; the expert must do that separately.
- An expert can only view, assign to, or read logs of their own linked clients.
- The invite flow (F1) automatically creates the link when the invited user accepts.

---

## Open Questions

- [ ] Should a client be able to request to be linked to a specific expert (vs. only expert-initiated)?
- [ ] Should there be a "pending / accepted" state on the relationship (client must accept the link)?
- [ ] When an expert unlinks a client, should existing assignments be automatically deactivated?
