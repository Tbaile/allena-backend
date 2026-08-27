<?php

namespace Database\Factories;

use App\Models\Exercise;
use App\Models\WorkoutPlan;
use App\Models\WorkoutPlanItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkoutPlanItem>
 */
class WorkoutPlanItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workout_plan_id' => WorkoutPlan::factory(),
            'exercise_id' => Exercise::factory(),
            'position' => fake()->unique()->numberBetween(1, 200),
            'sets' => fake()->numberBetween(2, 5),
            'reps' => fake()->numberBetween(6, 15),
            'duration_seconds' => null,
            'rest_seconds' => fake()->randomElement([60, 90, 120]),
            'target_weight' => fake()->randomFloat(2, 10, 120),
            'notes' => null,
        ];
    }

    /**
     * A timed hold: duration instead of reps.
     */
    public function timed(): self
    {
        return $this->state(fn (array $attributes): array => [
            'reps' => null,
            'duration_seconds' => fake()->randomElement([30, 45, 60]),
            'target_weight' => null,
        ]);
    }
}
