<?php

namespace App\Http\Requests;

use App\Models\WorkoutPlan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreWorkoutSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'started_at' => ['required', 'date'],
            'completed_at' => ['required', 'date', 'after_or_equal:started_at'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'sets' => ['required', 'array', 'min:1'],
            'sets.*.plan_item_id' => ['required', 'integer'],
            'sets.*.set_number' => ['required', 'integer', 'min:1', 'max:255'],
            'sets.*.reps' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'sets.*.weight' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'sets.*.duration_seconds' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            fn (Validator $validator) => $this->rejectForeignPlanItems($validator),
            fn (Validator $validator) => $this->rejectDuplicateSets($validator),
        ];
    }

    /**
     * A set may only reference an exercise prescribed by the plan being logged,
     * otherwise a client could write set logs onto somebody else's scheda.
     */
    private function rejectForeignPlanItems(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        /** @var WorkoutPlan $plan */
        $plan = $this->route('workoutPlan');
        $ownItemIds = $plan->items()->pluck('id')->all();

        foreach ($this->sets() as $index => $set) {
            if (! in_array($set['plan_item_id'], $ownItemIds, strict: false)) {
                $validator->errors()->add(
                    "sets.{$index}.plan_item_id",
                    'The selected exercise does not belong to this workout plan.',
                );
            }
        }
    }

    /**
     * The database enforces one row per (session, exercise, set number); catch
     * repeats here so a duplicated payload fails validation instead of the insert.
     */
    private function rejectDuplicateSets(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        $seen = [];

        foreach ($this->sets() as $set) {
            $key = $set['plan_item_id'].':'.$set['set_number'];

            if (isset($seen[$key])) {
                $validator->errors()->add('sets', 'Each set number may only be logged once per exercise.');

                return;
            }

            $seen[$key] = true;
        }
    }

    /**
     * @return array<int, array{plan_item_id: int, set_number: int, reps?: int|null, weight?: numeric-string|float|int|null, duration_seconds?: int|null}>
     */
    private function sets(): array
    {
        /** @var array<int, array{plan_item_id: int, set_number: int, reps?: int|null, weight?: numeric-string|float|int|null, duration_seconds?: int|null}> $sets */
        $sets = $this->validated('sets', []);

        return $sets;
    }
}
