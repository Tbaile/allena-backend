# F3 — Assignments

**Status:** planned

## Overview

An **Assignment** is the prescription of a specific exercise to a specific client by an expert. It defines *what* to do. One or more **AssignmentSchedule** rows define *when* and *how much* to do it each week (days of the week + sets/reps/duration).

A client can have multiple active assignments at once (different exercises on different days).

---

## Entities

### Assignment

| Field | Type | Notes |
|-------|------|-------|
| `id` | integer | |
| `expert_id` | FK → User | The assigning expert |
| `client_id` | FK → User | The receiving client |
| `exercise_id` | FK → Exercise | |
| `starts_at` | date | When this assignment becomes active |
| `ends_at` | date\|null | Optional end date; null = open-ended |
| `is_active` | boolean | Expert can pause/deactivate without deleting |
| `notes` | text\|null | Guidance text from the expert |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

### AssignmentSchedule

| Field | Type | Notes |
|-------|------|-------|
| `id` | integer | |
| `assignment_id` | FK → Assignment | |
| `day_of_week` | tinyint | 0 = Monday … 6 = Sunday |
| `sets` | tinyint\|null | |
| `reps` | tinyint\|null | |
| `duration_seconds` | integer\|null | Used instead of sets/reps for timed exercises |
| `created_at` | timestamp | |

---

## Endpoints

### Expert

| Method | URI | Description |
|--------|-----|-------------|
| `GET` | `/api/v1/clients/{clientId}/assignments` | List all assignments for a client |
| `POST` | `/api/v1/clients/{clientId}/assignments` | Create a new assignment |
| `GET` | `/api/v1/clients/{clientId}/assignments/{id}` | Get a single assignment |
| `PUT` | `/api/v1/clients/{clientId}/assignments/{id}` | Update assignment (notes, dates, active flag) |
| `DELETE` | `/api/v1/clients/{clientId}/assignments/{id}` | Delete an assignment |

### Client

| Method | URI | Description |
|--------|-----|-------------|
| `GET` | `/api/v1/me/assignments` | List own active assignments |
| `GET` | `/api/v1/me/assignments/today` | Today's scheduled exercises (derived from day_of_week) |
| `GET` | `/api/v1/me/assignments/{id}` | Get a single assignment detail |

---

## Request Shape — `POST /clients/{clientId}/assignments`

```json
{
  "exercise_id": 12,
  "starts_at": "2026-02-01",
  "ends_at": null,
  "notes": "Focus on slow, controlled movement. Stop if pain increases.",
  "schedules": [
    { "day_of_week": 0, "sets": 3, "reps": 10, "duration_seconds": null },
    { "day_of_week": 2, "sets": 3, "reps": 10, "duration_seconds": null },
    { "day_of_week": 4, "sets": 3, "reps": 10, "duration_seconds": null }
  ]
}
```

---

## Response Shape — Assignment

```json
{
  "id": 5,
  "exercise": {
    "id": 12,
    "name": "Seated Knee Extension",
    "category": { "id": 3, "name": "Free Body" },
    "tags": [{ "id": 7, "name": "knee" }],
    "video_url": null
  },
  "starts_at": "2026-02-01",
  "ends_at": null,
  "is_active": true,
  "notes": "Focus on slow, controlled movement. Stop if pain increases.",
  "schedules": [
    { "id": 1, "day_of_week": 0, "sets": 3, "reps": 10, "duration_seconds": null },
    { "id": 2, "day_of_week": 2, "sets": 3, "reps": 10, "duration_seconds": null },
    { "id": 3, "day_of_week": 4, "sets": 3, "reps": 10, "duration_seconds": null }
  ],
  "created_at": "2026-01-15T10:00:00Z"
}
```

---

## `GET /me/assignments/today`

Returns assignments where the current day of the week matches a schedule entry AND the assignment is active and within its date window. Each item includes whether the client has already logged it today.

```json
[
  {
    "assignment_id": 5,
    "schedule": { "day_of_week": 0, "sets": 3, "reps": 10, "duration_seconds": null },
    "exercise": { "id": 12, "name": "Seated Knee Extension", … },
    "already_logged_today": false
  }
]
```

---

## Business Rules

- An assignment must have at least one schedule entry.
- `sets` and `reps` are both null when `duration_seconds` is set, and vice versa (timed vs. rep-based exercises are mutually exclusive per schedule row).
- Only the owning expert (or admin) can modify or delete an assignment.
- Deactivating (`is_active = false`) hides the assignment from the client's view but preserves historical logs.
- An expert can only assign to clients that are linked to them (see F5).

---

## Open Questions

- [ ] Should a client be able to request an assignment change (feedback to expert)?
- [ ] Should there be a "pause" date range instead of just a boolean `is_active`?
- [ ] How should the `today` endpoint handle timezone differences between server and client?
