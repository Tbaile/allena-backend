<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateMeRequest;
use App\Http\Resources\MeResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeController extends Controller
{
    public function show(Request $request): JsonResource
    {
        return new MeResource($request->user());
    }

    public function update(UpdateMeRequest $request): JsonResource
    {
        /** @var User $user */
        $user = $request->user();

        if ($request->filled('name')) {
            $user->name = $request->string('name')->toString();
        }

        if ($request->filled('password')) {
            $user->password = $request->string('password')->toString();
            $user->must_change_password = false;
        }

        $user->save();

        return new MeResource($user);
    }
}
