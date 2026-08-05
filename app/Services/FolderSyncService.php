<?php

namespace App\Services;

use App\Models\FamilyMember;
use App\Models\User;

class FolderSyncService
{
    /**
     * Create a folder for the member when missing, or keep its name in sync
     * with the member's name.
     */
    public function syncForMember(FamilyMember $member): void
    {
        $folder = $member->folder()->first();

        if ($folder === null) {
            $member->user->folders()->create([
                'name' => $this->uniqueName($member->user, $member->name),
                'family_member_id' => $member->id,
            ]);

            return;
        }

        if ($folder->name !== $member->name) {
            $folder->name = $this->uniqueName($member->user, $member->name, $folder->id);
            $folder->save();
        }
    }

    /**
     * Delete the member's linked folder, leaving the contained media unfiled.
     */
    public function purgeForMember(FamilyMember $member): void
    {
        $member->folder()->first()?->delete();
    }

    /**
     * Resolve a folder name that is unique for the user, appending a numeric
     * suffix when the requested name is already taken.
     */
    public function uniqueName(User $user, string $name, ?int $excludeId = null): string
    {
        $candidate = $name;
        $suffix = 2;

        while ($this->nameExists($user, $candidate, $excludeId)) {
            $candidate = "{$name} {$suffix}";
            $suffix++;
        }

        return $candidate;
    }

    private function nameExists(User $user, string $name, ?int $excludeId): bool
    {
        return $user->folders()
            ->where('name', $name)
            ->when($excludeId !== null, fn ($query) => $query->whereKeyNot($excludeId))
            ->exists();
    }
}
