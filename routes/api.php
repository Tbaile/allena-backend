<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AvatarController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\MeController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\WorkoutPlanController;
use App\Http\Controllers\WorkoutSessionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:6,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('me', [MeController::class, 'show']);
        Route::put('me', [MeController::class, 'update']);
        Route::get('me/avatar', [AvatarController::class, 'show']);
        Route::post('me/avatar', [AvatarController::class, 'store']);
        Route::delete('me/avatar', [AvatarController::class, 'destroy']);
        Route::post('users/invite', [InviteController::class, 'inviteExpert']);
        Route::post('clients/invite', [InviteController::class, 'inviteClient']);

        Route::get('exercises', [ExerciseController::class, 'index']);
        Route::get('exercises/{exercise}', [ExerciseController::class, 'show']);
        Route::get('categories', [CategoryController::class, 'index']);
        Route::get('tags', [TagController::class, 'index']);

        Route::get('me/workout-plans', [WorkoutPlanController::class, 'index']);
        Route::get('me/workout-plans/{workoutPlan}', [WorkoutPlanController::class, 'show']);
        Route::post('me/workout-plans/{workoutPlan}/sessions', [WorkoutSessionController::class, 'store']);
        Route::get('me/workout-sessions', [WorkoutSessionController::class, 'index']);
    });
});
