<?php

namespace App\Http\Resources;

use App\Models\WorkoutPlanItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin WorkoutPlanItem */
class WorkoutPlanItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'position' => $this->position,
            'sets' => $this->sets,
            'reps' => $this->reps,
            'duration_seconds' => $this->duration_seconds,
            'rest_seconds' => $this->rest_seconds,
            'target_weight' => $this->target_weight === null ? null : (float) $this->target_weight,
            'notes' => $this->notes,
            'exercise' => new ExerciseResource($this->whenLoaded('exercise')),
        ];
    }
}
