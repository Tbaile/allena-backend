<?php

use App\Models\Exercise;
use App\Models\User;
use App\Models\WorkoutPlan;
use App\Models\WorkoutPlanItem;
use Spatie\Permission\Models\Role;

test('a client can list the workout plans assigned to them', function () {
    $client = User::factory()->create();
    $plan = WorkoutPlan::factory()->create(['client_id' => $client->id]);
    WorkoutPlanItem::factory()->create(['workout_plan_id' => $plan->id, 'position' => 1]);
    WorkoutPlan::factory()->create();

    $this->actingAs($client)
        ->getJson('/api/v1/me/workout-plans')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $plan->id)
        ->assertJsonStructure([
            'data' => [[
                'id', 'name', 'description', 'is_active', 'created_at',
                'items' => [[
                    'id', 'position', 'sets', 'reps', 'duration_seconds',
                    'rest_seconds', 'target_weight', 'notes',
                    'exercise' => ['id', 'name', 'category'],
                ]],
            ]],
            'meta' => ['current_page', 'per_page', 'total'],
        ]);
});

test('workout plans are paginated at 20 per page', function () {
    $client = User::factory()->create();
    WorkoutPlan::factory()->count(25)->create(['client_id' => $client->id]);

    $this->actingAs($client)
        ->getJson('/api/v1/me/workout-plans')
        ->assertOk()
        ->assertJsonCount(20, 'data')
        ->assertJsonPath('meta.per_page', 20)
        ->assertJsonPath('meta.total', 25);
});

test('an expert sees no plans in their own list', function () {
    $expert = User::factory()->create();
    WorkoutPlan::factory()->create(['expert_id' => $expert->id]);

    $this->actingAs($expert)
        ->getJson('/api/v1/me/workout-plans')
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

test('a client can view a single assigned plan with its items in order', function () {
    $client = User::factory()->create();
    $plan = WorkoutPlan::factory()->create(['client_id' => $client->id]);
    $squat = Exercise::factory()->create(['name' => 'Squat']);
    $bench = Exercise::factory()->create(['name' => 'Bench Press']);
    WorkoutPlanItem::factory()->create([
        'workout_plan_id' => $plan->id,
        'exercise_id' => $bench->id,
        'position' => 2,
    ]);
    WorkoutPlanItem::factory()->create([
        'workout_plan_id' => $plan->id,
        'exercise_id' => $squat->id,
        'position' => 1,
        'sets' => 4,
        'reps' => 8,
        'rest_seconds' => 90,
        'target_weight' => 80.5,
    ]);

    $this->actingAs($client)
        ->getJson("/api/v1/me/workout-plans/{$plan->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $plan->id)
        ->assertJsonCount(2, 'data.items')
        ->assertJsonPath('data.items.0.exercise.name', 'Squat')
        ->assertJsonPath('data.items.0.sets', 4)
        ->assertJsonPath('data.items.0.reps', 8)
        ->assertJsonPath('data.items.0.rest_seconds', 90)
        ->assertJsonPath('data.items.0.target_weight', 80.5)
        ->assertJsonPath('data.items.1.exercise.name', 'Bench Press');
});

test('a timed plan item reports duration instead of reps', function () {
    $client = User::factory()->create();
    $plan = WorkoutPlan::factory()->create(['client_id' => $client->id]);
    WorkoutPlanItem::factory()->timed()->create([
        'workout_plan_id' => $plan->id,
        'position' => 1,
        'duration_seconds' => 45,
    ]);

    $this->actingAs($client)
        ->getJson("/api/v1/me/workout-plans/{$plan->id}")
        ->assertOk()
        ->assertJsonPath('data.items.0.reps', null)
        ->assertJsonPath('data.items.0.duration_seconds', 45)
        ->assertJsonPath('data.items.0.target_weight', null);
});

test('a client cannot view a plan assigned to somebody else', function () {
    $plan = WorkoutPlan::factory()->create();

    $this->actingAs(User::factory()->create())
        ->getJson("/api/v1/me/workout-plans/{$plan->id}")
        ->assertForbidden();
});

test('an admin can view any plan', function () {
    $admin = tap(User::factory()->create())->assignRole(Role::findOrCreate('admin', 'web'));
    $plan = WorkoutPlan::factory()->create();

    $this->actingAs($admin)
        ->getJson("/api/v1/me/workout-plans/{$plan->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $plan->id);
});

test('unauthenticated users cannot list workout plans', function () {
    $this->getJson('/api/v1/me/workout-plans')->assertUnauthorized();
});

test('unauthenticated users cannot view a workout plan', function () {
    $plan = WorkoutPlan::factory()->create();

    $this->getJson("/api/v1/me/workout-plans/{$plan->id}")->assertUnauthorized();
});
