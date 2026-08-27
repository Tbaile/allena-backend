<?php

namespace App\Http\Resources;

use App\Models\WorkoutSetLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin WorkoutSetLog */
class WorkoutSetLogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'workout_plan_item_id' => $this->workout_plan_item_id,
            'set_number' => $this->set_number,
            'reps' => $this->reps,
            'weight' => $this->weight === null ? null : (float) $this->weight,
            'duration_seconds' => $this->duration_seconds,
        ];
    }
}
