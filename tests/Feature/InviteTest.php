<?php

use App\Mail\InviteMail;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Mail::fake();
});

// Authorization is verified in UserPolicyTest; here the policy is mocked so the
// endpoint behaviour is tested in isolation from role/permission seeding.

test('invite expert creates an expert when authorized', function () {
    Role::findOrCreate('expert', 'web');
    $this->mock(UserPolicy::class)->shouldReceive('inviteExpert')->andReturn(true);

    $this->actingAs(User::factory()->create())
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

test('invite expert is forbidden when unauthorized', function () {
    $this->mock(UserPolicy::class)->shouldReceive('inviteExpert')->andReturn(false);

    $this->actingAs(User::factory()->create())
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

test('invite client creates a client when authorized', function () {
    Role::findOrCreate('client', 'web');
    $this->mock(UserPolicy::class)->shouldReceive('inviteClient')->andReturn(true);

    $this->actingAs(User::factory()->create())
        ->postJson('/api/v1/clients/invite', [
            'name' => 'John Client',
            'email' => 'john@example.com',
        ])
        ->assertCreated()
        ->assertJsonPath('role', 'client');

    $invited = User::where('email', 'john@example.com')->first();
    expect($invited)->not->toBeNull()
        ->and($invited->hasRole('client'))->toBeTrue()
        ->and($invited->must_change_password)->toBeTrue();

    Mail::assertSent(InviteMail::class, fn ($mail) => $mail->hasTo('john@example.com'));
});

test('invite client is forbidden when unauthorized', function () {
    $this->mock(UserPolicy::class)->shouldReceive('inviteClient')->andReturn(false);

    $this->actingAs(User::factory()->create())
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

// The Authorize attribute runs as middleware, before validation, so these
// authorize the caller (mocked) to reach the validation layer.

test('invite requires name and email', function () {
    $this->mock(UserPolicy::class)->shouldReceive('inviteExpert')->andReturn(true);

    $this->actingAs(User::factory()->create())
        ->postJson('/api/v1/users/invite', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email']);
});

test('invite rejects duplicate email', function () {
    User::factory()->create(['email' => 'taken@example.com']);
    $this->mock(UserPolicy::class)->shouldReceive('inviteExpert')->andReturn(true);

    $this->actingAs(User::factory()->create())
        ->postJson('/api/v1/users/invite', [
            'name' => 'Someone',
            'email' => 'taken@example.com',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});
