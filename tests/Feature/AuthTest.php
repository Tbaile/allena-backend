<?php

use App\Models\User;

beforeEach(function () {
    $this->seed();
});

test('user can login with valid credentials', function () {
    $user = User::factory()->create(['password' => 'password123']);
    $user->assignRole('expert');

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'token',
            'user' => ['id', 'name', 'email', 'role'],
        ])
        ->assertJsonPath('user.email', $user->email)
        ->assertJsonPath('user.role', 'expert');
});

test('login fails with invalid password', function () {
    $user = User::factory()->create(['password' => 'password123']);

    $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertUnprocessable();
});

test('login fails with missing fields', function () {
    $this->postJson('/api/v1/auth/login', [])->assertUnprocessable();
});

test('user can logout', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/v1/auth/logout')
        ->assertOk();

    expect($user->tokens()->count())->toBe(0);
});

test('logout requires authentication', function () {
    $this->postJson('/api/v1/auth/logout')->assertUnauthorized();
});
