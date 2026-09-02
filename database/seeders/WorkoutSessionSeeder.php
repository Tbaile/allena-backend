<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WorkoutPlan;
use App\Models\WorkoutSession;
use App\Models\WorkoutSetLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class WorkoutSessionSeeder extends Seeder
{
    /**
     * Weeks before today that the demo client trained, oldest first. Week 4 is deliberately
     * missing so the progress chart shows one clear skipped week.
     */
    private const WEEKS_TRAINED = [7, 6, 5, 3, 2, 1];

    /**
     * Weight added to every prescribed load that week, keyed by weeks-ago. The client builds
     * up over weeks 7-5, misses week 4, then returns lighter and rebuilds without reaching the
     * earlier peak, so weekly volume drops after the skipped week.
     *
     * @var array<int, float>
     */
    private const WEIGHT_DELTA_KG = [
        7 => 0.0,
        6 => 2.5,
        5 => 5.0,
        3 => -2.5,
        2 => 0.0,
        1 => 2.5,
    ];

    public function run(): void
    {
        $client = User::where('email', 'customer@allena.app')->first();

        if ($client === null) {
            return;
        }

        $plan = WorkoutPlan::where('client_id', $client->id)
            ->where('name', WorkoutPlanSeeder::CHART_PLAN_NAME)
            ->with('items')
            ->first();

        if ($plan === null || $plan->items->isEmpty()) {
            return;
        }

        // Dates are relative to today, so re-running would pile up a second history.
        if ($plan->sessions()->exists()) {
            return;
        }

        foreach (self::WEEKS_TRAINED as $weeksAgo) {
            $this->seedSession($plan, $client, $weeksAgo);
        }
    }

    private function seedSession(WorkoutPlan $plan, User $client, int $weeksAgo): void
    {
        $startedAt = Carbon::now()
            ->subWeeks($weeksAgo)
            ->setTime(random_int(6, 21), random_int(0, 59));

        $addedWeight = self::WEIGHT_DELTA_KG[$weeksAgo] ?? 0.0;

        $session = WorkoutSession::create([
            'workout_plan_id' => $plan->id,
            'user_id' => $client->id,
            'started_at' => $startedAt,
            'completed_at' => $startedAt->copy()->addMinutes(random_int(38, 70)),
        ]);

        foreach ($plan->items as $item) {
            for ($setNumber = 1; $setNumber <= $item->sets; $setNumber++) {
                WorkoutSetLog::create([
                    'workout_session_id' => $session->id,
                    'workout_plan_item_id' => $item->id,
                    'set_number' => $setNumber,
                    'reps' => $item->reps,
                    'weight' => $item->target_weight === null
                        ? null
                        : (float) $item->target_weight + $addedWeight,
                    'duration_seconds' => $item->duration_seconds,
                ]);
            }
        }
    }
}
