<?php

namespace Database\Seeders;

use App\Models\Exercise;
use App\Models\User;
use App\Models\WorkoutPlan;
use Illuminate\Database\Seeder;

class WorkoutPlanSeeder extends Seeder
{
    /** The scheda that WorkoutSessionSeeder attaches 8 weeks of logged history to. */
    public const CHART_PLAN_NAME = 'Legs — Lower Body';

    public function run(): void
    {
        $expert = User::where('email', 'expert@allena.app')->first();
        $client = User::where('email', 'customer@allena.app')->first();

        if ($expert === null || $client === null) {
            return;
        }

        foreach ($this->plans() as [$name, $description, $items]) {
            $plan = WorkoutPlan::firstOrCreate(
                ['client_id' => $client->id, 'name' => $name],
                [
                    'description' => $description,
                    'expert_id' => $expert->id,
                    'is_active' => true,
                ],
            );

            foreach ($items as $position => [$exerciseName, $sets, $reps, $durationSeconds, $restSeconds, $targetWeight, $notes]) {
                $exercise = Exercise::where('name', $exerciseName)->first();

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

    /**
     * A push / pull / legs split, each scheda named after the body zone it targets.
     *
     * @return list<array{0: string, 1: string, 2: list<array{0: string, 1: int, 2: int|null, 3: int|null, 4: int, 5: float|null, 6: string|null}>}>
     */
    private function plans(): array
    {
        return [
            [
                'Push — Chest & Shoulders',
                'Chest, shoulders and triceps. Heavy pressing first, isolation work to finish. Warm up the shoulders for ten minutes before the first working set.',
                [
                    // [exercise, sets, reps, duration seconds, rest seconds, target weight, notes]
                    ['Bench Press', 4, 8, null, 120, 50.0, null],
                    ['Incline Bench Press', 3, 8, null, 90, 20.0, 'Smith machine, bench set to 43 degrees.'],
                    ['Overhead Press', 3, 8, null, 90, 30.0, 'Brace the core, no leg drive.'],
                    ['Lateral Raise', 3, 15, null, 45, 8.0, 'Lead with the elbows, no swing.'],
                    ['Tricep Pushdown', 3, 12, null, 45, 25.0, null],
                    ['Diamond Push-Up', 3, 12, null, 60, null, 'Bodyweight. Elbows tight to the ribs.'],
                ],
            ],
            [
                'Pull — Back & Biceps',
                'Back, rear delts and biceps. Pull from the floor, then rows and pulldowns, then arms and core.',
                [
                    ['Barbell Deadlift', 4, 5, null, 150, 80.0, 'Reset the bar on the floor between reps.'],
                    ['Barbell Row', 4, 8, null, 90, 40.0, 'Torso around 45 degrees, pull to the navel.'],
                    ['Lat Pulldown', 3, 12, null, 60, 45.0, null],
                    ['Seated Cable Row', 3, 12, null, 60, 40.0, null],
                    ['Face Pull', 3, 15, null, 45, 15.0, 'Pull to the forehead, external rotation at the end.'],
                    ['Barbell Curl', 3, 10, null, 45, 20.0, null],
                    ['Plank Hold', 3, null, 45, 60, null, 'Timed hold. Stop the set if the hips drop.'],
                ],
            ],
            [
                self::CHART_PLAN_NAME,
                'Quads, hamstrings, glutes and calves. Squat and hinge under load, then machine and unilateral work.',
                [
                    ['Barbell Back Squat', 4, 8, null, 120, 60.0, null],
                    ['Romanian Deadlift', 3, 10, null, 90, 40.0, 'Keep the bar against the legs, stop at mid-shin.'],
                    ['Leg Press', 3, 12, null, 90, 120.0, null],
                    ['Leg Curl', 3, 12, null, 60, 30.0, null],
                    ['Hip Thrust', 3, 10, null, 90, 70.0, 'Chin tucked, full lockout at the top.'],
                    ['Walking Lunge', 3, 20, null, 60, 20.0, 'Ten steps per leg, dumbbells at the sides.'],
                    ['Calf Raise', 4, 15, null, 45, 40.0, 'Pause one second at the top and bottom.'],
                ],
            ],
        ];
    }
}
