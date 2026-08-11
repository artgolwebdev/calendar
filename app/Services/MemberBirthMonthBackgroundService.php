<?php

namespace App\Services;

use App\Models\FamilyMember;
use App\Models\Media;
use App\Models\MonthPage;

class MemberBirthMonthBackgroundService
{
    /**
     * Set a freshly added photo as the auto background of the month the
     * member was born in.
     *
     * Conflict rules (first photo wins, never overwrite a manual background):
     * - A month that already has a manually-set background is left untouched.
     * - A month already claimed by another member's photo is left untouched.
     * - A photo uploaded for the same member replaces their own auto-set
     *   background (their latest photo wins for their birth month).
     */
    public function applyForPhoto(FamilyMember $member, Media $photo): void
    {
        $monthPage = $this->birthMonthPage($member);

        if ($monthPage === null) {
            return;
        }

        if ($monthPage->custom_image_path || $monthPage->background_media_id) {
            return;
        }

        if ($monthPage->auto_background_family_member_id !== null
            && $monthPage->auto_background_family_member_id !== $member->id) {
            return;
        }

        $monthPage->update([
            'auto_background_media_id' => $photo->id,
            'auto_background_family_member_id' => $member->id,
        ]);
    }

    /**
     * Re-sync the member's auto background after their birth date changed:
     * clear the assignment they previously owned, then re-apply using their
     * most recent photo (if any) to the new birth month.
     */
    public function syncForMember(FamilyMember $member): void
    {
        $this->clearForMember($member);

        $photo = $member->folder?->media()->orderByDesc('id')->first();

        if ($photo !== null) {
            $this->applyForPhoto($member, $photo);
        }
    }

    /**
     * Clear the auto background the member owned (used when the member is
     * deleted or no longer has any photo).
     */
    public function clearForMember(FamilyMember $member): void
    {
        MonthPage::where('auto_background_family_member_id', $member->id)
            ->update([
                'auto_background_media_id' => null,
                'auto_background_family_member_id' => null,
            ]);
    }

    /**
     * Clear the auto background referencing a deleted photo.
     */
    public function clearForPhoto(Media $photo): void
    {
        MonthPage::where('auto_background_media_id', $photo->id)
            ->update([
                'auto_background_media_id' => null,
                'auto_background_family_member_id' => null,
            ]);
    }

    /**
     * The member's birth-month page on their calendar, if it exists.
     */
    private function birthMonthPage(FamilyMember $member): ?MonthPage
    {
        if ($member->birth_date === null) {
            return null;
        }

        return $member->calendar->monthPages()
            ->where('month_number', $member->birth_date->month)
            ->first();
    }
}
