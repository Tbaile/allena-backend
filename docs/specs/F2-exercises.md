# F2 — Exercise Library

**Status:** planned

## Overview

A single global library of exercises shared across all experts. Exercises belong to one **category** and can carry multiple **tags**. Only experts (and admins) can create or modify exercises. All authenticated users can browse the library.

---

## Entities

### Category

| Field | Type | Notes |
|-------|------|-------|
| `id` | integer | |
| `name` | string | e.g. "Yoga", "Free Body", "Strength", "Cardio" |
| `slug` | string | URL-safe unique identifier |
| `created_at` | timestamp | |

### Tag

| Field | Type | Notes |
|-------|------|-------|
| `id` | integer | |
| `name` | string | e.g. "knee", "lower back", "posture", "flexibility" |
| `slug` | string | URL-safe unique identifier |
| `created_at` | timestamp | |

### Exercise

| Field | Type | Notes |
|-------|------|-------|
| `id` | integer | |
| `name` | string | |
| `description` | text | Instructions / what to do |
| `category_id` | FK → Category | |
| `video_url` | string\|null | **deferred** — stubbed on model, always null for now |
| `created_by` | FK → User | Expert who created it |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

**Pivot:** `exercise_tag` (exercise_id, tag_id)

---

## Endpoints

### Categories

| Method | URI | Auth | Description |
|--------|-----|------|-------------|
| `GET` | `/api/v1/categories` | any authenticated | List all categories |
| `POST` | `/api/v1/categories` | expert / admin | Create a category |
| `PUT` | `/api/v1/categories/{id}` | expert / admin | Update a category |
| `DELETE` | `/api/v1/categories/{id}` | admin only | Delete a category |

### Tags

| Method | URI | Auth | Description |
|--------|-----|------|-------------|
| `GET` | `/api/v1/tags` | any authenticated | List all tags |
| `POST` | `/api/v1/tags` | expert / admin | Create a tag |
| `PUT` | `/api/v1/tags/{id}` | expert / admin | Update a tag |
| `DELETE` | `/api/v1/tags/{id}` | admin only | Delete a tag |

### Exercises

| Method | URI | Auth | Description |
|--------|-----|------|-------------|
| `GET` | `/api/v1/exercises` | any authenticated | List exercises (with filters) |
| `GET` | `/api/v1/exercises/{id}` | any authenticated | Get single exercise |
| `POST` | `/api/v1/exercises` | expert / admin | Create exercise |
| `PUT` | `/api/v1/exercises/{id}` | expert / admin | Update exercise |
| `DELETE` | `/api/v1/exercises/{id}` | admin only | Delete exercise |

---

## Query Parameters — `GET /exercises`

| Param | Type | Description |
|-------|------|-------------|
| `category` | string (slug) | Filter by category |
| `tag` | string (slug) | Filter by tag (can pass multiple) |
| `search` | string | Full-text search on name + description |
| `per_page` | integer | Pagination, default 20 |

---

## Response Shape — Exercise

```json
{
  "id": 1,
  "name": "Seated Knee Extension",
  "description": "Sit upright, extend leg slowly…",
  "category": {
    "id": 3,
    "name": "Free Body",
    "slug": "free-body"
  },
  "tags": [
    { "id": 7, "name": "knee", "slug": "knee" }
  ],
  "video_url": null,
  "created_at": "2026-01-01T00:00:00Z"
}
```

---

## Business Rules

- Category and tag names must be unique.
- Deleting a category is only allowed when no exercises reference it.
- Deleting a tag removes the pivot rows but does not delete exercises.
- `video_url` field exists on the model but returns `null` until the video feature is implemented.

---

## Open Questions

- [ ] Should categories support a hierarchy (e.g. "Strength > Olympic Lifting")?
- [ ] Should tags be grouped by type (body area vs. benefit vs. equipment)?
- [ ] Who can delete an exercise — only its creator, or any expert/admin?
