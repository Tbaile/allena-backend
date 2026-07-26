<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexExerciseRequest;
use App\Http\Resources\ExerciseResource;
use App\Models\Exercise;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ExerciseController extends Controller
{
    #[Endpoint(title: 'List exercises', description: 'Browse the shared exercise library. Filter by category slug (filter[category]), tag slug (filter[tag]), or a search term matching name/description (filter[search]). Paginated at 20 per page.')]
    #[Group('Exercises')]
    public function index(IndexExerciseRequest $request): AnonymousResourceCollection
    {
        $exercises = QueryBuilder::for(Exercise::class)
            ->allowedFilters(
                AllowedFilter::exact('category', 'category.slug'),
                AllowedFilter::exact('tag', 'tags.slug'),
                AllowedFilter::callback('search', function (Builder $query, mixed $value): void {
                    if (! is_string($value)) {
                        return;
                    }

                    $query->where(function (Builder $q) use ($value): void {
                        $q->where('name', 'ilike', "%{$value}%")
                            ->orWhere('description', 'ilike', "%{$value}%");
                    });
                })->delimiter(''),
            )
            ->with(['category', 'tags'])
            ->paginate(20);

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
