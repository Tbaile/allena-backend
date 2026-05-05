<?php

namespace App\Http\Requests;

use App\Models\User;

class InviteExpertRequest extends InviteRequest
{
    public function authorize(): bool
    {
        /** @var User $user */
        $user = $this->user();

        return $user->can('invite-expert');
    }
}
