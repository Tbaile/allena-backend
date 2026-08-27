<?php

namespace App\Http\Resources;

use App\Models\WorkoutSession;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin WorkoutSession */
class WorkoutSessionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'workout_plan_id' => $this->workout_plan_id,
            'plan_name' => $this->whenLoaded('plan', fn (): ?string => $this->plan?->name),
            'started_at' => $this->started_at->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'notes' => $this->notes,
            'set_count' => $this->whenCounted('setLogs'),
            'total_volume' => $this->whenHas('total_volume', fn (): float => is_numeric($this->total_volume) ? (float) $this->total_volume : 0.0),
            'set_logs' => WorkoutSetLogResource::collection($this->whenLoaded('setLogs')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
