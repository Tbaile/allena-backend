<?php

use App\Models\User;
use App\Models\WorkoutPlan;
use App\Models\WorkoutPlanItem;
use App\Models\WorkoutSession;
use App\Models\WorkoutSetLog;

function planFor(User $client): WorkoutPlan
{
    return WorkoutPlan::factory()->create(['client_id' => $client->id]);
}

test('a client can upload a finished session with its sets', function () {
    $client = User::factory()->create();
    $plan = planFor($client);
    $item = WorkoutPlanItem::factory()->create(['workout_plan_id' => $plan->id, 'position' => 1]);

    $this->actingAs($client)
        ->postJson("/api/v1/me/workout-plans/{$plan->id}/sessions", [
            'started_at' => '2026-08-27T18:00:00Z',
            'completed_at' => '2026-08-27T18:45:00Z',
            'notes' => 'Felt strong.',
            'sets' => [
                ['plan_item_id' => $item->id, 'set_number' => 1, 'reps' => 10, 'weight' => 50],
                ['plan_item_id' => $item->id, 'set_number' => 2, 'reps' => 8, 'weight' => 50],
            ],
        ])
        ->assertCreated()
        ->assertJsonPath('data.workout_plan_id', $plan->id)
        ->assertJsonPath('data.notes', 'Felt strong.')
        ->assertJsonCount(2, 'data.set_logs')
        ->assertJsonPath('data.set_logs.0.set_number', 1)
        ->assertJsonPath('data.set_logs.0.reps', 10)
        ->assertJsonPath('data.set_logs.0.weight', 50);

    $session = WorkoutSession::sole();
    expect($session->user_id)->toBe($client->id)
        ->and($session->workout_plan_id)->toBe($plan->id)
        ->and($session->setLogs)->toHaveCount(2);
});

test('a timed set is logged with a duration and no reps', function () {
    $client = User::factory()->create();
    $plan = planFor($client);
    $item = WorkoutPlanItem::factory()->timed()->create(['workout_plan_id' => $plan->id, 'position' => 1]);

    $this->actingAs($client)
        ->postJson("/api/v1/me/workout-plans/{$plan->id}/sessions", [
            'started_at' => '2026-08-27T18:00:00Z',
            'completed_at' => '2026-08-27T18:10:00Z',
            'sets' => [
                ['plan_item_id' => $item->id, 'set_number' => 1, 'duration_seconds' => 45],
            ],
        ])
        ->assertCreated()
        ->assertJsonPath('data.set_logs.0.duration_seconds', 45)
        ->assertJsonPath('data.set_logs.0.reps', null)
        ->assertJsonPath('data.set_logs.0.weight', null);
});

test('a client cannot log a session against somebody elses plan', function () {
    $plan = WorkoutPlan::factory()->create();
    $item = WorkoutPlanItem::factory()->create(['workout_plan_id' => $plan->id, 'position' => 1]);

    $this->actingAs(User::factory()->create())
        ->postJson("/api/v1/me/workout-plans/{$plan->id}/sessions", [
            'started_at' => '2026-08-27T18:00:00Z',
            'completed_at' => '2026-08-27T18:45:00Z',
            'sets' => [['plan_item_id' => $item->id, 'set_number' => 1, 'reps' => 10]],
        ])
        ->assertForbidden();

    expect(WorkoutSession::count())->toBe(0);
});

test('sets belonging to a different plan are rejected', function () {
    $client = User::factory()->create();
    $plan = planFor($client);
    $otherPlan = planFor($client);
    $foreignItem = WorkoutPlanItem::factory()->create(['workout_plan_id' => $otherPlan->id, 'position' => 1]);

    $this->actingAs($client)
        ->postJson("/api/v1/me/workout-plans/{$plan->id}/sessions", [
            'started_at' => '2026-08-27T18:00:00Z',
            'completed_at' => '2026-08-27T18:45:00Z',
            'sets' => [['plan_item_id' => $foreignItem->id, 'set_number' => 1, 'reps' => 10]],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('sets.0.plan_item_id');

    expect(WorkoutSession::count())->toBe(0);
});

test('a session must contain at least one set', function () {
    $client = User::factory()->create();
    $plan = planFor($client);

    $this->actingAs($client)
        ->postJson("/api/v1/me/workout-plans/{$plan->id}/sessions", [
            'started_at' => '2026-08-27T18:00:00Z',
            'completed_at' => '2026-08-27T18:45:00Z',
            'sets' => [],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('sets');
});

test('a session cannot finish before it started', function () {
    $client = User::factory()->create();
    $plan = planFor($client);
    $item = WorkoutPlanItem::factory()->create(['workout_plan_id' => $plan->id, 'position' => 1]);

    $this->actingAs($client)
        ->postJson("/api/v1/me/workout-plans/{$plan->id}/sessions", [
            'started_at' => '2026-08-27T18:45:00Z',
            'completed_at' => '2026-08-27T18:00:00Z',
            'sets' => [['plan_item_id' => $item->id, 'set_number' => 1, 'reps' => 10]],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('completed_at');
});

test('the same set number cannot be logged twice for one exercise', function () {
    $client = User::factory()->create();
    $plan = planFor($client);
    $item = WorkoutPlanItem::factory()->create(['workout_plan_id' => $plan->id, 'position' => 1]);

    $this->actingAs($client)
        ->postJson("/api/v1/me/workout-plans/{$plan->id}/sessions", [
            'started_at' => '2026-08-27T18:00:00Z',
            'completed_at' => '2026-08-27T18:45:00Z',
            'sets' => [
                ['plan_item_id' => $item->id, 'set_number' => 1, 'reps' => 10],
                ['plan_item_id' => $item->id, 'set_number' => 1, 'reps' => 8],
            ],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('sets');

    expect(WorkoutSession::count())->toBe(0);
});

test('a client can list their session history newest first with volume', function () {
    $client = User::factory()->create();
    $plan = planFor($client);
    $item = WorkoutPlanItem::factory()->create(['workout_plan_id' => $plan->id, 'position' => 1]);

    $older = WorkoutSession::factory()->create([
        'workout_plan_id' => $plan->id,
        'user_id' => $client->id,
        'started_at' => '2026-08-01T10:00:00Z',
    ]);
    $newer = WorkoutSession::factory()->create([
        'workout_plan_id' => $plan->id,
        'user_id' => $client->id,
        'started_at' => '2026-08-20T10:00:00Z',
    ]);
    WorkoutSetLog::factory()->count(2)->sequence(
        ['set_number' => 1, 'reps' => 10, 'weight' => 50],
        ['set_number' => 2, 'reps' => 8, 'weight' => 50],
    )->create([
        'workout_session_id' => $newer->id,
        'workout_plan_item_id' => $item->id,
    ]);

    WorkoutSession::factory()->create();

    $this->actingAs($client)
        ->getJson('/api/v1/me/workout-sessions')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.id', $newer->id)
        ->assertJsonPath('data.1.id', $older->id)
        ->assertJsonPath('data.0.plan_name', $plan->name)
        ->assertJsonPath('data.0.set_count', 2)
        ->assertJsonPath('data.0.total_volume', 900)
        ->assertJsonPath('data.1.set_count', 0)
        ->assertJsonPath('data.1.total_volume', 0)
        ->assertJsonStructure([
            'data' => [['id', 'workout_plan_id', 'plan_name', 'started_at', 'completed_at', 'set_count', 'total_volume']],
            'meta' => ['current_page', 'per_page', 'total'],
        ]);
});

test('session history is paginated at 20 per page', function () {
    $client = User::factory()->create();
    $plan = planFor($client);
    WorkoutSession::factory()->count(25)->create([
        'workout_plan_id' => $plan->id,
        'user_id' => $client->id,
    ]);

    $this->actingAs($client)
        ->getJson('/api/v1/me/workout-sessions')
        ->assertOk()
        ->assertJsonCount(20, 'data')
        ->assertJsonPath('meta.per_page', 20)
        ->assertJsonPath('meta.total', 25);
});

test('unauthenticated users cannot log a session', function () {
    $plan = WorkoutPlan::factory()->create();

    $this->postJson("/api/v1/me/workout-plans/{$plan->id}/sessions", [])->assertUnauthorized();
});

test('unauthenticated users cannot read session history', function () {
    $this->getJson('/api/v1/me/workout-sessions')->assertUnauthorized();
});
