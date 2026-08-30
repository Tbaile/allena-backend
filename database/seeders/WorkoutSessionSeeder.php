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
     * Weeks before today that the demo client trained. The gaps are deliberate: the progress
     * chart should show rest weeks, not an unbroken run of bars.
     */
    private const WEEKS_TRAINED = [7, 6, 4, 3, 1, 0];

    /** Added to each prescribed weight per week, so the chart trends upwards. */
    private const WEEKLY_PROGRESSION_KG = 2.5;

    public function run(): void
    {
        $client = User::where('email', 'customer@fairly.app')->first();

        if ($client === null) {
            return;
        }

        $plan = WorkoutPlan::where('client_id', $client->id)->with('items')->first();

        if ($plan === null || $plan->items->isEmpty()) {
            return;
        }

        // Dates are relative to today, so re-running would pile up a second history.
        if ($plan->sessions()->exists()) {
            return;
        }

        $oldest = max(self::WEEKS_TRAINED);

        foreach (self::WEEKS_TRAINED as $weeksAgo) {
            $this->seedSession($plan, $client, $weeksAgo, $oldest);
        }
    }

    private function seedSession(WorkoutPlan $plan, User $client, int $weeksAgo, int $oldest): void
    {
        $startedAt = Carbon::now()->subWeeks($weeksAgo)->setTime(18, 0);
        $addedWeight = (($oldest - $weeksAgo) * self::WEEKLY_PROGRESSION_KG);

        $session = WorkoutSession::create([
            'workout_plan_id' => $plan->id,
            'user_id' => $client->id,
            'started_at' => $startedAt,
            'completed_at' => $startedAt->copy()->addMinutes(52),
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
