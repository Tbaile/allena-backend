<?php

use App\Models\User;
use App\Models\WorkoutPlan;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('admin', 'web');
    Role::findOrCreate('expert', 'web');
    Role::findOrCreate('client', 'web');
});

test('only the assigned client may view a workout plan', function () {
    $client = tap(User::factory()->create())->assignRole('client');
    $stranger = tap(User::factory()->create())->assignRole('client');
    $plan = WorkoutPlan::factory()->create(['client_id' => $client->id]);

    expect(Gate::forUser($client)->allows('view', $plan))->toBeTrue()
        ->and(Gate::forUser($stranger)->allows('view', $plan))->toBeFalse();
});

test('the authoring expert may not view a plan they assigned', function () {
    $expert = tap(User::factory()->create())->assignRole('expert');
    $plan = WorkoutPlan::factory()->create(['expert_id' => $expert->id]);

    expect(Gate::forUser($expert)->allows('view', $plan))->toBeFalse();
});

test('admins may view any workout plan through the gate bypass', function () {
    $admin = tap(User::factory()->create())->assignRole('admin');
    $plan = WorkoutPlan::factory()->create();

    expect(Gate::forUser($admin)->allows('view', $plan))->toBeTrue();
});
