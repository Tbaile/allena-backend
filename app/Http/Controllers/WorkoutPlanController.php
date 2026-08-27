<?php

namespace App\Http\Controllers;

use App\Http\Resources\WorkoutPlanResource;
use App\Models\User;
use App\Models\WorkoutPlan;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Attributes\Controllers\Authorize;

class WorkoutPlanController extends Controller
{
    #[Endpoint(title: 'List my workout plans', description: 'The schede assigned to the authenticated client, newest first, with their exercises in prescribed order. Paginated at 20 per page.')]
    #[Group('Workouts')]
    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = $request->user();

        $plans = WorkoutPlan::query()
            ->where('client_id', $user->id)
            ->with(['items.exercise.category'])
            ->latest()
            ->paginate(20);

        return WorkoutPlanResource::collection($plans);
    }

    #[Endpoint(title: 'Get workout plan', description: 'A single scheda with its exercises in prescribed order. Only the client the plan is assigned to may read it.')]
    #[Group('Workouts')]
    #[Authorize('view', 'workoutPlan')]
    public function show(WorkoutPlan $workoutPlan): WorkoutPlanResource
    {
        $workoutPlan->load(['items.exercise.category']);

        return new WorkoutPlanResource($workoutPlan);
    }
}
