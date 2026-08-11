<?php

namespace App\Services;

use App\Models\FamilyMember;
use Illuminate\Http\UploadedFile;

class FamilyMemberMediaService
{
    public function __construct(protected MemberBirthMonthBackgroundService $memberBirthMonthBackground) {}

    /**
     * Persist uploaded images into the member's linked media folder.
     *
     * Each stored photo also becomes the auto background of the member's
     * birth month (first photo wins per month; manual backgrounds are never
     * overwritten).
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

            $this->memberBirthMonthBackground->applyForPhoto($member, $media);
        }
    }
}
