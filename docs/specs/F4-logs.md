# F4 — Exercise Logs

**Status:** planned

## Overview

When a client completes a scheduled exercise, they create an **ExerciseLog** entry. The log records what they actually did (actual sets, reps, or duration) plus an optional note. Experts can view a client's full log history to track adherence.

---

## Entity

### ExerciseLog

| Field | Type | Notes |
|-------|------|-------|
| `id` | integer | |
| `assignment_id` | FK → Assignment | |
| `client_id` | FK → User | Denormalised for easy querying |
| `logged_at` | date | The date the exercise was performed |
| `actual_sets` | tinyint\|null | |
| `actual_reps` | tinyint\|null | |
| `actual_duration_seconds` | integer\|null | |
| `notes` | text\|null | Client's own note (how it felt, modifications) |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

---

## Endpoints

### Client

| Method | URI | Description |
|--------|-----|-------------|
| `POST` | `/api/v1/me/assignments/{assignmentId}/logs` | Log a completion |
| `GET` | `/api/v1/me/logs` | List own log history (with filters) |
| `GET` | `/api/v1/me/logs/{id}` | Get single log entry |
| `PUT` | `/api/v1/me/logs/{id}` | Update a log entry |
| `DELETE` | `/api/v1/me/logs/{id}` | Delete a log entry |

### Expert

| Method | URI | Description |
|--------|-----|-------------|
| `GET` | `/api/v1/clients/{clientId}/logs` | View a client's full log history |

---

## Request Shape — `POST /me/assignments/{assignmentId}/logs`

```json
{
  "logged_at": "2026-02-03",
  "actual_sets": 3,
  "actual_reps": 8,
  "actual_duration_seconds": null,
  "notes": "Felt some discomfort on the third set, stopped early."
}
```

---

## Response Shape — ExerciseLog

```json
{
  "id": 42,
  "assignment_id": 5,
  "exercise": {
    "id": 12,
    "name": "Seated Knee Extension"
  },
  "logged_at": "2026-02-03",
  "actual_sets": 3,
  "actual_reps": 8,
  "actual_duration_seconds": null,
  "notes": "Felt some discomfort on the third set, stopped early.",
  "created_at": "2026-02-03T18:30:00Z"
}
```

---

## Query Parameters — `GET /me/logs`

| Param | Type | Description |
|-------|------|-------------|
| `from` | date (YYYY-MM-DD) | Start of date range |
| `to` | date (YYYY-MM-DD) | End of date range |
| `assignment_id` | integer | Filter by a specific assignment |
| `per_page` | integer | Pagination, default 20 |

---

## Business Rules

- A client can only log against their own active assignments.
- Multiple logs for the same assignment on the same day are allowed (e.g. the client did two sessions).
- Edit and delete are permitted; no hard time restriction in v1 (open question below).
- `actual_sets`/`actual_reps` and `actual_duration_seconds` follow the same mutual exclusion as the assignment schedule, but are not strictly enforced — the client may record partial data.
- An expert viewing `/clients/{clientId}/logs` can only access logs for clients linked to them.

---

## Open Questions

- [ ] Should there be a grace period for editing/deleting logs (e.g. within 24 hours only)?
- [ ] Should the API expose an adherence summary (e.g. % of scheduled sessions logged this week)?
- [ ] Should logs be immutable once reviewed/acknowledged by the expert?
