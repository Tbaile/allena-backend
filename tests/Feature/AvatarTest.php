<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * A real 1x1 JPEG. The php container has no GD/Imagick, so UploadedFile::fake()->image()
 * is unavailable and the bytes are inlined instead.
 */
function jpegPhoto(string $name = 'avatar.jpg'): UploadedFile
{
    $bytes = base64_decode(
        '/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRof'.
        'Hh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/wAALCAABAAEBAREA/8QAFAAB'.
        'AAAAAAAAAAAAAAAAAAAACf/EABQQAQAAAAAAAAAAAAAAAAAAAAD/2gAIAQEAAD8AKp//2Q=='
    );

    return UploadedFile::fake()->createWithContent($name, $bytes);
}

test('me exposes a null avatar_url when no photo was uploaded', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson('/api/v1/me')
        ->assertOk()
        ->assertJsonPath('data.avatar_url', null);
});

test('user can upload an avatar', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/api/v1/me/avatar', ['photo' => jpegPhoto()], ['Accept' => 'application/json'])
        ->assertOk()
        ->assertJsonPath('data.avatar_url', fn (?string $url): bool => is_string($url) && str_contains($url, '/api/v1/me/avatar'));

    $path = $user->fresh()->avatar_path;

    expect($path)->toBeString();
    Storage::disk('public')->assertExists($path);
});

test('uploading a new avatar deletes the previous file', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $this->actingAs($user)->post('/api/v1/me/avatar', ['photo' => jpegPhoto('first.jpg')], ['Accept' => 'application/json'])->assertOk();
    $first = $user->fresh()->avatar_path;

    $this->actingAs($user)->post('/api/v1/me/avatar', ['photo' => jpegPhoto('second.jpg')], ['Accept' => 'application/json'])->assertOk();
    $second = $user->fresh()->avatar_path;

    expect($second)->not->toBe($first);
    Storage::disk('public')->assertMissing($first);
    Storage::disk('public')->assertExists($second);
});

test('a non image file is rejected', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/api/v1/me/avatar', ['photo' => UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf')], ['Accept' => 'application/json'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['photo']);

    expect($user->fresh()->avatar_path)->toBeNull();
});

test('an oversized image is rejected', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/api/v1/me/avatar', ['photo' => UploadedFile::fake()->create('huge.jpg', 6000, 'image/jpeg')], ['Accept' => 'application/json'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['photo']);
});

test('the photo field is required', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/api/v1/me/avatar', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['photo']);
});

test('user can download their avatar', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $this->actingAs($user)->post('/api/v1/me/avatar', ['photo' => jpegPhoto()], ['Accept' => 'application/json'])->assertOk();

    $this->actingAs($user)
        ->get('/api/v1/me/avatar')
        ->assertOk()
        ->assertHeader('content-type', 'image/jpeg');
});

test('downloading a missing avatar returns 404', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->getJson('/api/v1/me/avatar')->assertNotFound();
});

test('user can remove their avatar', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $this->actingAs($user)->post('/api/v1/me/avatar', ['photo' => jpegPhoto()], ['Accept' => 'application/json'])->assertOk();
    $path = $user->fresh()->avatar_path;

    $this->actingAs($user)
        ->deleteJson('/api/v1/me/avatar')
        ->assertOk()
        ->assertJsonPath('data.avatar_url', null);

    expect($user->fresh()->avatar_path)->toBeNull();
    Storage::disk('public')->assertMissing($path);
});

test('removing a missing avatar is a no-op', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->deleteJson('/api/v1/me/avatar')
        ->assertOk()
        ->assertJsonPath('data.avatar_url', null);
});

test('unauthenticated users cannot use the avatar endpoints', function () {
    $this->getJson('/api/v1/me/avatar')->assertUnauthorized();
    $this->postJson('/api/v1/me/avatar', [])->assertUnauthorized();
    $this->deleteJson('/api/v1/me/avatar')->assertUnauthorized();
});
