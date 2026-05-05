<?php

namespace App\Http\Controllers;

use App\Http\Requests\InviteClientRequest;
use App\Http\Requests\InviteExpertRequest;
use App\Http\Requests\InviteRequest;
use App\Http\Resources\UserResource;
use App\Mail\InviteMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InviteController extends Controller
{
    public function inviteExpert(InviteExpertRequest $request): JsonResponse
    {
        return $this->invite($request, 'expert');
    }

    public function inviteClient(InviteClientRequest $request): JsonResponse
    {
        return $this->invite($request, 'client');
    }

    private function invite(InviteRequest $request, string $role): JsonResponse
    {
        $temporaryPassword = Str::password(12);

        $user = User::create([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'password' => $temporaryPassword,
            'must_change_password' => true,
        ]);

        $user->assignRole($role);

        Mail::to($user->email)->send(new InviteMail($user, $temporaryPassword));

        return response()->json(new UserResource($user), 201);
    }
}
