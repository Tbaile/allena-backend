<?php

namespace App\Models;

use Database\Factories\WorkoutPlanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'description', 'expert_id', 'client_id', 'is_active'])]
class WorkoutPlan extends Model
{
    /** @use HasFactory<WorkoutPlanFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<WorkoutPlanItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(WorkoutPlanItem::class)->orderBy('position');
    }

    /**
     * @return HasMany<WorkoutSession, $this>
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(WorkoutSession::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function expert(): BelongsTo
    {
        return $this->belongsTo(User::class, 'expert_id');
    }
}
