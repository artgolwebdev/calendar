<?php

namespace App\Observers;

use App\Models\FamilyMember;
use App\Services\FamilyEventGeneratorService;
use App\Services\FolderSyncService;
use App\Services\MemberBirthMonthBackgroundService;

class FamilyMemberObserver
{
    public function created(FamilyMember $familyMember): void
    {
        app(FamilyEventGeneratorService::class)->syncForMember($familyMember);
        app(FolderSyncService::class)->syncForMember($familyMember);
    }

    public function updated(FamilyMember $familyMember): void
    {
        if ($familyMember->wasChanged('calendar_id')) {
            app(FamilyEventGeneratorService::class)->purgeForMember($familyMember);
        }

        if ($familyMember->wasChanged('birth_date')) {
            app(MemberBirthMonthBackgroundService::class)->syncForMember($familyMember);
        }

        app(FamilyEventGeneratorService::class)->syncForMember($familyMember);
        app(FolderSyncService::class)->syncForMember($familyMember);
    }

    public function deleting(FamilyMember $familyMember): void
    {
        app(FamilyEventGeneratorService::class)->purgeForMember($familyMember);
        app(FolderSyncService::class)->purgeForMember($familyMember);
        app(MemberBirthMonthBackgroundService::class)->clearForMember($familyMember);
    }
}
