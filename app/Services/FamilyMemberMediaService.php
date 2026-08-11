<?php

namespace App\Services;

use App\Models\FamilyMember;
use Illuminate\Http\UploadedFile;

class FamilyMemberMediaService
{
    /**
     * Persist uploaded images into the member's linked media folder.
     *
     * @param  array<int, UploadedFile>  $files
     */
    public function storeImages(FamilyMember $member, array $files): void
    {
        $user = $member->calendar->user;
        $folderId = $member->folder()->first()?->id;

        foreach ($files as $file) {
            $media = $user->addMedia($file)->toMediaCollection('user_media');
            $media->folder_id = $folderId;
            $media->save();
        }
    }
}
