<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Exercise;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ExerciseSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect(['Yoga', 'Free Body', 'Strength', 'Cardio'])
            ->mapWithKeys(fn (string $name) => [
                $name => Category::firstOrCreate(
                    ['slug' => Str::slug($name)],
                    ['name' => $name],
                ),
            ]);

        $tags = collect(['knee', 'lower back', 'posture', 'flexibility', 'mobility', 'core', 'balance'])
            ->mapWithKeys(fn (string $name) => [
                $name => Tag::firstOrCreate(
                    ['slug' => Str::slug($name)],
                    ['name' => $name],
                ),
            ]);

        $exercises = [
            ['Seated Knee Extension', 'Sit upright, extend one leg slowly until straight, hold, then lower.', 'Free Body', ['knee', 'mobility']],
            ['Cat-Cow Stretch', 'On all fours, alternate arching and rounding your spine with the breath.', 'Yoga', ['lower back', 'flexibility', 'posture']],
            ['Downward Dog', 'From all fours, lift hips up and back into an inverted V, heels toward the floor.', 'Yoga', ['flexibility', 'posture', 'mobility']],
            ['Barbell Back Squat', 'With the bar across your upper back, squat down keeping the chest up, then drive up.', 'Strength', ['core', 'balance']],
            ['Plank Hold', 'Hold a straight line from head to heels on forearms and toes, bracing the core.', 'Free Body', ['core', 'posture']],
            ['Jumping Jacks', 'Jump while spreading legs and raising arms overhead, then return, at a steady pace.', 'Cardio', ['mobility', 'balance']],
            ['Glute Bridge', 'Lie on your back, knees bent, and lift hips until shoulders, hips and knees align.', 'Free Body', ['lower back', 'core']],
            ['High Knees', 'Run in place driving knees up to hip height with a quick, rhythmic tempo.', 'Cardio', ['mobility', 'balance']],
        ];

        foreach ($exercises as [$name, $description, $categoryName, $tagNames]) {
            $exercise = Exercise::firstOrCreate(
                ['name' => $name],
                [
                    'description' => $description,
                    'category_id' => $categories[$categoryName]->id,
                    'video_url' => null,
                ],
            );

            $exercise->tags()->syncWithoutDetaching(
                collect($tagNames)->map(fn (string $t) => $tags[$t]->id)->all(),
            );
        }
    }
}
