<?php

namespace App\Http\Controllers;

use App\Http\Requests\InviteClientRequest;
use App\Http\Requests\InviteExpertRequest;
use App\Http\Requests\InviteRequest;
use App\Http\Resources\UserResource;
use App\Mail\InviteMail;
use App\Models\User;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InviteController extends Controller
{
    #[Endpoint(title: 'Invite expert', description: 'Create a new expert account. A temporary password is sent by email. The invited user must change their password on first login.')]
    #[Group('Invite')]
    #[Response(status: 201, type: 'UserResource')]
    public function inviteExpert(InviteExpertRequest $request): JsonResponse
    {
        $user = $this->createUser($request, 'expert');

        return response()->json(new UserResource($user), 201);
    }

    #[Endpoint(title: 'Invite client', description: 'Create a new client account. A temporary password is sent by email. The invited user must change their password on first login.')]
    #[Group('Invite')]
    #[Response(status: 201, type: 'UserResource')]
    public function inviteClient(InviteClientRequest $request): JsonResponse
    {
        $user = $this->createUser($request, 'client');

        return response()->json(new UserResource($user), 201);
    }

    private function createUser(InviteRequest $request, string $role): User
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

        return $user;
    }
}
