<?php

namespace App\Models;

use Database\Factories\WorkoutSetLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'workout_session_id',
    'workout_plan_item_id',
    'set_number',
    'reps',
    'weight',
    'duration_seconds',
])]
class WorkoutSetLog extends Model
{
    /** @use HasFactory<WorkoutSetLogFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<WorkoutSession, $this>
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(WorkoutSession::class, 'workout_session_id');
    }

    /**
     * @return BelongsTo<WorkoutPlanItem, $this>
     */
    public function planItem(): BelongsTo
    {
        return $this->belongsTo(WorkoutPlanItem::class, 'workout_plan_item_id');
    }
}
