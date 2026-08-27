<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WorkoutPlan;
use App\Models\WorkoutSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkoutSession>
 */
class WorkoutSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = fake()->dateTimeBetween('-8 weeks');

        return [
            'workout_plan_id' => WorkoutPlan::factory(),
            'user_id' => User::factory(),
            'started_at' => $startedAt,
            'completed_at' => (clone $startedAt)->modify('+45 minutes'),
            'notes' => null,
        ];
    }
}
