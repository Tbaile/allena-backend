<?php

namespace Database\Factories;

use App\Models\WorkoutPlanItem;
use App\Models\WorkoutSession;
use App\Models\WorkoutSetLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkoutSetLog>
 */
class WorkoutSetLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workout_session_id' => WorkoutSession::factory(),
            'workout_plan_item_id' => WorkoutPlanItem::factory(),
            'set_number' => 1,
            'reps' => fake()->numberBetween(6, 15),
            'weight' => fake()->randomFloat(2, 10, 120),
            'duration_seconds' => null,
        ];
    }
}
