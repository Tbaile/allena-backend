<?php

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Permission::findOrCreate('invite-client', 'web');
    Role::findOrCreate('admin', 'web');
    Role::findOrCreate('expert', 'web')->givePermissionTo('invite-client');
    Role::findOrCreate('client', 'web');
});

test('only admins may invite an expert', function () {
    $admin = tap(User::factory()->create())->assignRole('admin');
    $expert = tap(User::factory()->create())->assignRole('expert');
    $client = tap(User::factory()->create())->assignRole('client');

    expect(Gate::forUser($admin)->allows('inviteExpert', User::class))->toBeTrue()
        ->and(Gate::forUser($expert)->allows('inviteExpert', User::class))->toBeFalse()
        ->and(Gate::forUser($client)->allows('inviteExpert', User::class))->toBeFalse();
});

test('experts and admins may invite a client', function () {
    $admin = tap(User::factory()->create())->assignRole('admin');
    $expert = tap(User::factory()->create())->assignRole('expert');
    $client = tap(User::factory()->create())->assignRole('client');

    expect(Gate::forUser($admin)->allows('inviteClient', User::class))->toBeTrue()
        ->and(Gate::forUser($expert)->allows('inviteClient', User::class))->toBeTrue()
        ->and(Gate::forUser($client)->allows('inviteClient', User::class))->toBeFalse();
});
