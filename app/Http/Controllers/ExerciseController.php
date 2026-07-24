<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexExerciseRequest;
use App\Http\Resources\ExerciseResource;
use App\Models\Exercise;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ExerciseController extends Controller
{
    #[Endpoint(title: 'List exercises', description: 'Browse the shared exercise library. Filter by category slug, tag slug, or a search term matching name/description. Paginated.')]
    #[Group('Exercises')]
    public function index(IndexExerciseRequest $request): AnonymousResourceCollection
    {
        $exercises = Exercise::query()
            ->with(['category', 'tags'])
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->whereHas('category', fn ($q) => $q->where('slug', $request->string('category')));
            })
            ->when($request->filled('tag'), function ($query) use ($request) {
                $query->whereHas('tags', fn ($q) => $q->where('slug', $request->string('tag')));
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                        ->orWhere('description', 'ilike', "%{$search}%");
                });
            })
            ->paginate($request->integer('per_page', 20));

        return ExerciseResource::collection($exercises);
    }

    #[Endpoint(title: 'Get exercise', description: 'Returns a single exercise with its category and tags.')]
    #[Group('Exercises')]
    public function show(Exercise $exercise): ExerciseResource
    {
        $exercise->load(['category', 'tags']);

        return new ExerciseResource($exercise);
    }
}
