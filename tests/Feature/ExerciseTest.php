<?php

use App\Models\Category;
use App\Models\Exercise;
use App\Models\Tag;
use App\Models\User;

test('authenticated user can list exercises', function () {
    Exercise::factory()->count(3)->create();

    $this->actingAs(User::factory()->create())
        ->getJson('/api/v1/exercises')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [['id', 'name', 'description', 'category', 'tags', 'video_url', 'created_at']],
            'meta' => ['current_page', 'per_page', 'total'],
        ]);
});

test('exercises can be filtered by category slug', function () {
    $yoga = Category::factory()->create(['slug' => 'yoga']);
    $cardio = Category::factory()->create(['slug' => 'cardio']);
    Exercise::factory()->for($yoga, 'category')->create();
    Exercise::factory()->for($cardio, 'category')->create();

    $this->actingAs(User::factory()->create())
        ->getJson('/api/v1/exercises?category=yoga')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.category.slug', 'yoga');
});

test('exercises can be filtered by tag slug', function () {
    $knee = Tag::factory()->create(['slug' => 'knee']);
    $withTag = Exercise::factory()->create();
    $withTag->tags()->attach($knee);
    Exercise::factory()->create();

    $this->actingAs(User::factory()->create())
        ->getJson('/api/v1/exercises?tag=knee')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $withTag->id);
});

test('exercises can be searched by name and description', function () {
    Exercise::factory()->create(['name' => 'Seated Knee Extension', 'description' => 'Extend leg.']);
    Exercise::factory()->create(['name' => 'Plank Hold', 'description' => 'Brace the knee area.']);
    Exercise::factory()->create(['name' => 'Jumping Jacks', 'description' => 'Cardio move.']);

    $this->actingAs(User::factory()->create())
        ->getJson('/api/v1/exercises?search=knee')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

test('exercises are paginated by per_page', function () {
    Exercise::factory()->count(5)->create();

    $this->actingAs(User::factory()->create())
        ->getJson('/api/v1/exercises?per_page=2')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('meta.per_page', 2)
        ->assertJsonPath('meta.total', 5);
});

test('authenticated user can view a single exercise', function () {
    $tag = Tag::factory()->create();
    $exercise = Exercise::factory()->create();
    $exercise->tags()->attach($tag);

    $this->actingAs(User::factory()->create())
        ->getJson("/api/v1/exercises/{$exercise->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $exercise->id)
        ->assertJsonPath('data.category.id', $exercise->category_id)
        ->assertJsonPath('data.tags.0.id', $tag->id)
        ->assertJsonPath('data.video_url', null);
});

test('unauthenticated user cannot list exercises', function () {
    $this->getJson('/api/v1/exercises')->assertUnauthorized();
});

test('unauthenticated user cannot view a single exercise', function () {
    $exercise = Exercise::factory()->create();

    $this->getJson("/api/v1/exercises/{$exercise->id}")->assertUnauthorized();
});
