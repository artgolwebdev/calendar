<?php

namespace App\Services;

use App\Models\Calendar;
use App\Models\FamilyMember;
use App\Models\Folder;

class FolderSyncService
{
    /**
     * Create the calendar's root folder when missing, or keep its name in
     * sync with the calendar's name.
     */
    public function ensureRootFor(Calendar $calendar): Folder
    {
        $root = $calendar->folders()->whereNull('parent_id')->first();

        if ($root === null) {
            return $calendar->folders()->create([
                'user_id' => $calendar->user_id,
                'name' => $calendar->name,
            ]);
        }

        if ($root->name !== $calendar->name) {
            $root->name = $calendar->name;
            $root->save();
        }

        return $root;
    }

    /**
     * Create a nested folder for the member under the calendar's root when
     * missing, or keep it in sync with the member's name and calendar.
     */
    public function syncForMember(FamilyMember $member): void
    {
        $root = $this->ensureRootFor($member->calendar);
        $folder = $member->folder()->first();

        if ($folder === null) {
            $member->calendar->folders()->create([
                'user_id' => $root->user_id,
                'parent_id' => $root->id,
                'name' => $this->uniqueName($root, $member->name),
                'family_member_id' => $member->id,
            ]);

            return;
        }

        if ($folder->parent_id !== $root->id) {
            $folder->parent_id = $root->id;
            $folder->calendar_id = $member->calendar_id;
            $folder->user_id = $root->user_id;
        }

        if ($folder->name !== $member->name) {
            $folder->name = $this->uniqueName($root, $member->name, $folder->id);
        }

        $folder->save();
    }

    /**
     * Delete the member's linked folder, leaving the contained media unfiled.
     */
    public function purgeForMember(FamilyMember $member): void
    {
        $member->folder()->first()?->delete();
    }

    /**
     * Resolve a folder name that is unique among the parent's children,
     * appending a numeric suffix when the requested name is already taken.
     */
    public function uniqueName(Folder $parent, string $name, ?int $excludeId = null): string
    {
        $candidate = $name;
        $suffix = 2;

        while ($this->nameExists($parent, $candidate, $excludeId)) {
            $candidate = "{$name} {$suffix}";
            $suffix++;
        }

        return $candidate;
    }

    private function nameExists(Folder $parent, string $name, ?int $excludeId): bool
    {
        return Folder::where('parent_id', $parent->id)
            ->where('name', $name)
            ->when($excludeId !== null, fn ($query) => $query->whereKeyNot($excludeId))
            ->exists();
    }
}
