<?php

use App\Models\User;

beforeEach(function () {
    $this->seed();
});

test('authenticated user can view their profile', function () {
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

test('user can update their password and must_change_password is cleared', function () {
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

test('password must be at least 8 characters', function () {
    $user = User::factory()->create();

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
