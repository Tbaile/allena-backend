<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Only admins may invite experts. Admins are granted implicitly by the
     * Gate::before bypass, so this method covers non-admins (always denied).
     */
    public function inviteExpert(): bool
    {
        return false;
    }

    /**
     * Experts (and admins, via the Gate::before bypass) may invite clients.
     */
    public function inviteClient(User $user): bool
    {
        return $user->can('invite-client');
    }
}
