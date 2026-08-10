<?php

namespace App\Console\Commands;

use App\Models\FamilyMember;
use App\Services\FolderSyncService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('folders:backfill')]
#[Description('Create a media folder for every existing family member that lacks one')]
class FoldersBackfill extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(FolderSyncService $folders): int
    {
        $members = FamilyMember::with('calendar')->whereDoesntHave('folder')->get();

        foreach ($members as $member) {
            $folders->syncForMember($member);
        }

        $this->info("Folders synced for {$members->count()} family members.");

        return self::SUCCESS;
    }
}
