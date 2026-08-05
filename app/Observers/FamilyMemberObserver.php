<?php

namespace App\Observers;

use App\Models\FamilyMember;
use App\Services\FamilyEventGeneratorService;

class FamilyMemberObserver
{
    public function created(FamilyMember $familyMember): void
    {
        app(FamilyEventGeneratorService::class)->syncForMember($familyMember);
    }

    public function updated(FamilyMember $familyMember): void
    {
        app(FamilyEventGeneratorService::class)->syncForMember($familyMember);
    }

    public function deleting(FamilyMember $familyMember): void
    {
        app(FamilyEventGeneratorService::class)->purgeForMember($familyMember);
    }
}
