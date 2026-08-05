<?php

namespace App\Policies;

use App\Models\Media;
use App\Models\User;

class MediaPolicy
{
    /**
     * Determine whether the user can view any media.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the media.
     */
    public function view(User $user, Media $media): bool
    {
        return $this->owns($user, $media);
    }

    /**
     * Determine whether the user can create media.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the media.
     */
    public function update(User $user, Media $media): bool
    {
        return $this->owns($user, $media);
    }

    /**
     * Determine whether the user can delete the media.
     */
    public function delete(User $user, Media $media): bool
    {
        return $this->owns($user, $media);
    }

    /**
     * Whether the media belongs to the given user's media library.
     */
    private function owns(User $user, Media $media): bool
    {
        return $media->model_type === User::class && $media->model_id === $user->id;
    }
}
