<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkoutSessionRequest;
use App\Http\Resources\WorkoutSessionResource;
use App\Models\User;
use App\Models\WorkoutPlan;
use App\Models\WorkoutSession;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use Illuminate\Support\Facades\DB;

class WorkoutSessionController extends Controller
{
    #[Endpoint(title: 'List my session history', description: 'Workouts the authenticated client has completed, newest first, each with its logged set count and total volume (sum of reps x weight). Paginated at 20 per page.')]
    #[Group('Workouts')]
    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = $request->user();

        $sessions = WorkoutSession::query()
            ->where('user_id', $user->id)
            ->with('plan')
            ->withCount('setLogs')
            ->withSum('setLogs as total_volume', DB::raw('reps * weight'))
            ->orderByDesc('started_at')
            ->paginate(20);

        return WorkoutSessionResource::collection($sessions);
    }

    #[Endpoint(title: 'Log a completed workout', description: 'Upload one finished workout in a single request: the session plus every set performed. The client records sets locally while training and posts them all on completion, so a workout survives losing connectivity.')]
    #[Group('Workouts')]
    #[Response(status: 201, type: 'WorkoutSessionResource')]
    #[Authorize('logSession', 'workoutPlan')]
    public function store(StoreWorkoutSessionRequest $request, WorkoutPlan $workoutPlan): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $session = DB::transaction(function () use ($request, $workoutPlan, $user): WorkoutSession {
            $session = $workoutPlan->sessions()->create([
                'user_id' => $user->id,
                'started_at' => $request->date('started_at'),
                'completed_at' => $request->date('completed_at'),
                'notes' => $request->input('notes'),
            ]);

            /** @var array<int, array<string, mixed>> $sets */
            $sets = $request->validated('sets');

            $session->setLogs()->createMany(array_map(fn (array $set): array => [
                'workout_plan_item_id' => $set['plan_item_id'],
                'set_number' => $set['set_number'],
                'reps' => $set['reps'] ?? null,
                'weight' => $set['weight'] ?? null,
                'duration_seconds' => $set['duration_seconds'] ?? null,
            ], $sets));

            return $session;
        });

        $session->load(['plan', 'setLogs']);

        return (new WorkoutSessionResource($session))->response()->setStatusCode(201);
    }
}
