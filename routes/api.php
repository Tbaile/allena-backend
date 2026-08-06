<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\MeController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:6,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('me', [MeController::class, 'show']);
        Route::put('me', [MeController::class, 'update']);
        Route::post('users/invite', [InviteController::class, 'inviteExpert']);
        Route::post('clients/invite', [InviteController::class, 'inviteClient']);

        Route::get('exercises', [ExerciseController::class, 'index']);
        Route::get('exercises/{exercise}', [ExerciseController::class, 'show']);
        Route::get('categories', [CategoryController::class, 'index']);
        Route::get('tags', [TagController::class, 'index']);
    });
});
