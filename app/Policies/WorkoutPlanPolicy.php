<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkoutPlan;

class WorkoutPlanPolicy
{
    /**
     * A plan is visible only to the client it was assigned to. Admins are
     * granted implicitly by the Gate::before bypass.
     */
    public function view(User $user, WorkoutPlan $workoutPlan): bool
    {
        return $workoutPlan->client_id === $user->id;
    }

    /**
     * Only the client a plan was assigned to may log workouts against it.
     */
    public function logSession(User $user, WorkoutPlan $workoutPlan): bool
    {
        return $this->view($user, $workoutPlan);
    }
}
