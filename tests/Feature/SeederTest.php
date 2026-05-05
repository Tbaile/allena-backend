<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

test('roles are seeded', function () {
    $this->seed();

    expect(Role::pluck('name')->sort()->values()->all())
        ->toBe(['admin', 'client', 'expert']);
});

test('admin user is seeded from env variables', function () {
    $this->seed();

    $admin = User::where('email', config('app.admin_email'))->first();

    expect($admin)->not->toBeNull()
        ->and($admin->hasRole('admin'))->toBeTrue()
        ->and($admin->must_change_password)->toBeFalse();
});

test('seeder is idempotent', function () {
    $this->seed();
    $this->seed();

    expect(User::count())->toBe(1)
        ->and(Role::count())->toBe(3);
});
