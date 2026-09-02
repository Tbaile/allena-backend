<?php

namespace Database\Seeders;

use App\Models\Exercise;
use App\Models\User;
use App\Models\WorkoutPlan;
use Illuminate\Database\Seeder;

class WorkoutPlanSeeder extends Seeder
{
    public function run(): void
    {
        $expert = User::where('email', 'expert@allena.app')->first();
        $client = User::where('email', 'customer@allena.app')->first();

        if ($expert === null || $client === null) {
            return;
        }

        $plan = WorkoutPlan::firstOrCreate(
            ['client_id' => $client->id, 'name' => 'Full Body A'],
            [
                'description' => 'Lower-body power and strength paired with upper-body pressing and pulling. Warm up for ten minutes before the first working set.',
                'expert_id' => $expert->id,
                'is_active' => true,
            ],
        );

        // [exercise, sets, reps, duration seconds, rest seconds, target weight, notes]
        $items = [
            ['Jump Squat', 4, 5, null, 120, 10.0, 'Hold a 10 kg plate at the chest. Land soft, reset between reps.'],
            ['Barbell Back Squat', 4, 8, null, 120, 60.0, null],
            ['Incline Bench Press', 3, 8, null, 90, 20.0, 'Smith machine, bench set to 43 degrees.'],
            ['Romanian Deadlift', 3, 10, null, 90, 40.0, 'Keep the bar against the legs, stop at mid-shin.'],
            ['Lat Pulldown', 3, 12, null, 60, 45.0, null],
            ['Plank Hold', 3, null, 45, 60, null, 'Timed hold. Stop the set if the hips drop.'],
        ];

        foreach ($items as $position => [$name, $sets, $reps, $durationSeconds, $restSeconds, $targetWeight, $notes]) {
            $exercise = Exercise::where('name', $name)->first();

            if ($exercise === null) {
                continue;
            }

            $plan->items()->firstOrCreate(
                ['position' => $position + 1],
                [
                    'exercise_id' => $exercise->id,
                    'sets' => $sets,
                    'reps' => $reps,
                    'duration_seconds' => $durationSeconds,
                    'rest_seconds' => $restSeconds,
                    'target_weight' => $targetWeight,
                    'notes' => $notes,
                ],
            );
        }
    }
}
