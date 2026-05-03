# Fairly — Application Overview

## Product Summary

Fairly is a fitness planner that connects **experts** (personal trainers, physiotherapists, coaches) with their **clients**. The expert builds a personalised programme — choosing exercises, defining a weekly schedule, and adding guidance. The client sees their daily plan and logs completed sessions. The expert monitors adherence.

## Roles

Managed via Spatie Laravel Permission. Two base roles ship with the application:

| Role | Description |
|------|-------------|
| `expert` | Creates/manages exercises, assigns programmes to clients, views client logs |
| `client` | Views own programme, logs completions |
| `admin` | Full access (internal use) |

## Domain Model

```
User
 └── has role (expert | client | admin)

Category
 └── name (yoga, free body, strength, …)

Tag
 └── name (knee, back, posture, flexibility, …)

Exercise
 ├── name, description
 ├── belongs to Category
 ├── has many Tags (pivot)
 └── video_url (deferred)

Assignment  — expert prescribes an exercise to a client
 ├── expert_id  → User
 ├── client_id  → User
 ├── exercise_id → Exercise
 ├── starts_at, ends_at (optional window)
 ├── is_active
 └── notes (expert guidance)

AssignmentSchedule  — weekly recurrence for an Assignment
 ├── assignment_id
 ├── day_of_week (0 = Monday … 6 = Sunday)
 ├── sets
 ├── reps
 └── duration_seconds

ExerciseLog  — client's actual completion record
 ├── assignment_id
 ├── client_id
 ├── logged_at
 ├── actual_sets, actual_reps, actual_duration_seconds
 └── notes
```

## Feature Index

| ID | Feature | Status | Spec |
|----|---------|--------|------|
| F1 | Authentication & Users | planned | [F1-auth.md](F1-auth.md) |
| F2 | Exercise Library | planned | [F2-exercises.md](F2-exercises.md) |
| F3 | Assignments | planned | [F3-assignments.md](F3-assignments.md) |
| F4 | Exercise Logs | planned | [F4-logs.md](F4-logs.md) |
| F5 | Expert–Client Relationships | planned | [F5-relationships.md](F5-relationships.md) |

## Deferred Features

These are acknowledged but out of scope for the initial build:

- Video management (upload / S3 / streaming)
- Push notifications (daily reminders)
- Progress analytics (charts, streaks, adherence %)
- Multi-expert organisations (gym/org tenant layer)
- Exercise programme templates (reusable preset collections)
- In-app messaging between expert and client
- iOS app support

## Technical Decisions

| Concern | Choice | Notes |
|---------|--------|-------|
| Auth | Laravel Sanctum | Token-based, suited for mobile |
| Roles & permissions | Spatie Laravel Permission | Roles: `expert`, `client`, `admin` |
| Response format | Eloquent API Resources | |
| API documentation | [Scramble](https://scramble.dedoc.co/) | Auto-generates OpenAPI 3.1 from routes + resources; UI is swappable |
| Database | PostgreSQL | Consistent environment via Docker |
| Testing | Pest v4 | Feature tests against real DB, no mocks |
| Static analysis | Larastan level 10 | |
| Code style | Laravel Pint | |

## Suggested Build Order

1. Install Spatie permission + Scramble; scaffold `routes/api.php` with `/api/v1/` prefix and Sanctum middleware
2. **F1** — Auth endpoints, role seeding
3. **F2** — Exercise library (Category, Tag, Exercise)
4. **F3** — Assignments + schedules
5. **F4** — Exercise logs
6. **F5** — Expert–client relationship endpoints
