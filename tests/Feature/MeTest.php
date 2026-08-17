<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

test('authenticated user can view their profile', function () {
    Role::findOrCreate('client', 'web');
    $user = User::factory()->create(['must_change_password' => true]);
    $user->assignRole('client');

    $this->actingAs($user)
        ->getJson('/api/v1/me')
        ->assertOk()
        ->assertJsonStructure([
            'data' => ['id', 'name', 'email', 'role', 'must_change_password', 'created_at'],
        ])
        ->assertJsonPath('data.email', $user->email)
        ->assertJsonPath('data.role', 'client')
        ->assertJsonPath('data.must_change_password', true);
});

test('unauthenticated user cannot view profile', function () {
    $this->getJson('/api/v1/me')->assertUnauthorized();
});

test('user can update their name', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->putJson('/api/v1/me', ['name' => 'New Name'])
        ->assertOk()
        ->assertJsonPath('data.name', 'New Name');

    expect($user->fresh()->name)->toBe('New Name');
});

test('forced password change does not require the current password', function () {
    $user = User::factory()->create(['must_change_password' => true]);

    $this->actingAs($user)
        ->putJson('/api/v1/me', [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])
        ->assertOk()
        ->assertJsonPath('data.must_change_password', false);

    expect($user->fresh()->must_change_password)->toBeFalse();
});

test('changing password on a normal account requires the current password', function () {
    $user = User::factory()->create(['must_change_password' => false]);

    $this->actingAs($user)
        ->putJson('/api/v1/me', [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['current_password']);
});

test('changing password succeeds with the correct current password', function () {
    $user = User::factory()->create(['must_change_password' => false]);

    $this->actingAs($user)
        ->putJson('/api/v1/me', [
            'current_password' => 'password',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])
        ->assertOk();

    expect(Hash::check('newpassword123', $user->fresh()->password))->toBeTrue();
});

test('changing password fails with an incorrect current password', function () {
    $user = User::factory()->create(['must_change_password' => false]);

    $this->actingAs($user)
        ->putJson('/api/v1/me', [
            'current_password' => 'wrong-password',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['current_password']);
});

test('password must be at least 8 characters', function () {
    $user = User::factory()->create(['must_change_password' => true]);

    $this->actingAs($user)
        ->putJson('/api/v1/me', [
            'password' => 'short',
            'password_confirmation' => 'short',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['password']);
});

test('unauthenticated user cannot update profile', function () {
    $this->putJson('/api/v1/me', ['name' => 'Hacker'])->assertUnauthorized();
});
