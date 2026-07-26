<?php

use App\Models\Tag;
use App\Models\User;

test('authenticated user can list tags', function () {
    Tag::factory()->count(3)->create();

    $this->actingAs(User::factory()->create())
        ->getJson('/api/v1/tags')
        ->assertOk()
        ->assertJsonStructure(['data' => [['id', 'name', 'slug']]]);
});

test('unauthenticated user cannot list tags', function () {
    $this->getJson('/api/v1/tags')->assertUnauthorized();
});
