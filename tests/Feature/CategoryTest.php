<?php

use App\Models\Category;
use App\Models\User;

test('authenticated user can list categories', function () {
    Category::factory()->count(3)->create();

    $this->actingAs(User::factory()->create())
        ->getJson('/api/v1/categories')
        ->assertOk()
        ->assertJsonStructure(['data' => [['id', 'name', 'slug']]]);
});

test('unauthenticated user cannot list categories', function () {
    $this->getJson('/api/v1/categories')->assertUnauthorized();
});
