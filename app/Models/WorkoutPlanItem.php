<?php

namespace App\Models;

use Database\Factories\WorkoutPlanItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'workout_plan_id',
    'exercise_id',
    'position',
    'sets',
    'reps',
    'duration_seconds',
    'rest_seconds',
    'target_weight',
    'notes',
])]
class WorkoutPlanItem extends Model
{
    /** @use HasFactory<WorkoutPlanItemFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'target_weight' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<WorkoutPlan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(WorkoutPlan::class, 'workout_plan_id');
    }

    /**
     * @return BelongsTo<Exercise, $this>
     */
    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }
}
