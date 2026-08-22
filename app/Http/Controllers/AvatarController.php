<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAvatarRequest;
use App\Http\Resources\MeResource;
use App\Models\User;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AvatarController extends Controller
{
    #[Endpoint(title: 'Download avatar', description: "Streams the authenticated user's profile photo. Returns 404 when no photo has been uploaded.")]
    #[Group('Me')]
    #[Response(description: 'The raw image bytes.')]
    public function show(Request $request): StreamedResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_if($user->avatar_path === null, 404);

        return Storage::disk('public')->response($user->avatar_path);
    }

    #[Endpoint(title: 'Upload avatar', description: "Stores a new profile photo for the authenticated user, replacing any previous one. Accepts a multipart 'photo' field of at most 5 MB.")]
    #[Group('Me')]
    public function store(StoreAvatarRequest $request): JsonResource
    {
        /** @var User $user */
        $user = $request->user();

        $this->deleteStoredAvatar($user);

        $path = $request->file('photo')->store('avatars', 'public');

        $user->avatar_path = is_string($path) ? $path : null;
        $user->save();

        return new MeResource($user);
    }

    #[Endpoint(title: 'Remove avatar', description: "Deletes the authenticated user's profile photo. Succeeds even when there is nothing to delete.")]
    #[Group('Me')]
    public function destroy(Request $request): JsonResource
    {
        /** @var User $user */
        $user = $request->user();

        $this->deleteStoredAvatar($user);

        $user->avatar_path = null;
        $user->save();

        return new MeResource($user);
    }

    private function deleteStoredAvatar(User $user): void
    {
        if ($user->avatar_path !== null) {
            Storage::disk('public')->delete($user->avatar_path);
        }
    }
}
