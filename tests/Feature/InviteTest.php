<?php

use App\Mail\InviteMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->seed();
    Mail::fake();
});

// Admin invites expert

test('admin can invite an expert', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->postJson('/api/v1/users/invite', [
            'name' => 'Jane Expert',
            'email' => 'jane@example.com',
        ])
        ->assertCreated()
        ->assertJsonStructure(['id', 'name', 'email', 'role'])
        ->assertJsonPath('role', 'expert');

    $invited = User::where('email', 'jane@example.com')->first();
    expect($invited)->not->toBeNull()
        ->and($invited->hasRole('expert'))->toBeTrue()
        ->and($invited->must_change_password)->toBeTrue();

    Mail::assertSent(InviteMail::class, fn ($mail) => $mail->hasTo('jane@example.com'));
});

test('non-admin cannot invite an expert', function () {
    $expert = User::factory()->create();
    $expert->assignRole('expert');

    $this->actingAs($expert)
        ->postJson('/api/v1/users/invite', [
            'name' => 'Jane Expert',
            'email' => 'jane@example.com',
        ])
        ->assertForbidden();
});

test('unauthenticated user cannot invite an expert', function () {
    $this->postJson('/api/v1/users/invite', [
        'name' => 'Jane Expert',
        'email' => 'jane@example.com',
    ])->assertUnauthorized();
});

// Expert invites client

test('expert can invite a client', function () {
    $expert = User::factory()->create();
    $expert->assignRole('expert');

    $this->actingAs($expert)
        ->postJson('/api/v1/clients/invite', [
            'name' => 'John Client',
            'email' => 'john@example.com',
        ])
        ->assertCreated()
        ->assertJsonStructure(['id', 'name', 'email', 'role'])
        ->assertJsonPath('role', 'client');

    $invited = User::where('email', 'john@example.com')->first();
    expect($invited)->not->toBeNull()
        ->and($invited->hasRole('client'))->toBeTrue()
        ->and($invited->must_change_password)->toBeTrue();

    Mail::assertSent(InviteMail::class, fn ($mail) => $mail->hasTo('john@example.com'));
});

test('non-expert cannot invite a client', function () {
    $client = User::factory()->create();
    $client->assignRole('client');

    $this->actingAs($client)
        ->postJson('/api/v1/clients/invite', [
            'name' => 'John Client',
            'email' => 'john@example.com',
        ])
        ->assertForbidden();
});

test('unauthenticated user cannot invite a client', function () {
    $this->postJson('/api/v1/clients/invite', [
        'name' => 'John Client',
        'email' => 'john@example.com',
    ])->assertUnauthorized();
});

// Validation

test('invite requires name and email', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->postJson('/api/v1/users/invite', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email']);
});

test('invite rejects duplicate email', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    User::factory()->create(['email' => 'taken@example.com']);

    $this->actingAs($admin)
        ->postJson('/api/v1/users/invite', [
            'name' => 'Someone',
            'email' => 'taken@example.com',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});
