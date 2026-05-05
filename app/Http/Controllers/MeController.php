<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateMeRequest;
use App\Http\Resources\MeResource;
use App\Models\User;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeController extends Controller
{
    #[Endpoint(title: 'Get profile', description: "Returns the authenticated user's profile including role and password change status.")]
    #[Group('Me')]
    public function show(Request $request): JsonResource
    {
        return new MeResource($request->user());
    }

    #[Endpoint(title: 'Update profile', description: "Update the authenticated user's name and/or password. Changing the password clears the must_change_password flag.")]
    #[Group('Me')]
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
