<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class MeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->getRoleNames()->first(),
            'must_change_password' => $this->must_change_password,
            'avatar_url' => $this->avatarUrl(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }

    /**
     * The version suffix busts the client image cache when the photo is replaced.
     */
    private function avatarUrl(): ?string
    {
        if ($this->avatar_path === null) {
            return null;
        }

        return url('/api/v1/me/avatar').'?v='.substr(sha1($this->avatar_path), 0, 8);
    }
}
