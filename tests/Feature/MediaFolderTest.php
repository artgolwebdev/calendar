<?php

namespace Tests\Feature;

use App\Models\Calendar;
use App\Models\FamilyMember;
use App\Models\Folder;
use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaFolderTest extends TestCase
{
    use RefreshDatabase;

    private function createCalendarFor(User $user): Calendar
    {
        return $user->calendars()->create(['name' => 'לוח משפחתי']);
    }

    /**
     * @param  array<int, string>  $names
     * @return array{0: User, 1: Collection<int, Media>}
     */
    private function createUserWithMedia(array $names = ['family.jpg']): array
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $media = collect();
        foreach ($names as $name) {
            $media->push($user->addMedia(UploadedFile::fake()->image($name))->toMediaCollection('user_media'));
        }

        return [$user, $media];
    }

    public function test_guest_cannot_manage_folders(): void
    {
        $this->post('/folders', ['name' => 'חופשה'])->assertRedirect('/login');
        $this->put('/folders/1', ['name' => 'טיולים'])->assertRedirect('/login');
        $this->delete('/folders/1')->assertRedirect('/login');
    }

    public function test_user_can_create_folder(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/folders', ['name' => 'חופשה']);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('folders', [
            'user_id' => $user->id,
            'name' => 'חופשה',
            'family_member_id' => null,
        ]);
    }

    public function test_folder_name_must_be_unique_per_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->actingAs($user)->post('/folders', ['name' => 'חופשה']);
        $this->actingAs($user)->post('/folders', ['name' => 'חופשה'])->assertSessionHasErrors('name');

        $this->actingAs($otherUser)->post('/folders', ['name' => 'חופשה'])->assertRedirect();

        $this->assertDatabaseCount('folders', 2);
    }

    public function test_creating_family_member_creates_linked_folder(): void
    {
        $user = User::factory()->create();
        $calendar = $this->createCalendarFor($user);

        $this->actingAs($user)->post("/calendars/{$calendar->id}/members", [
            'name' => 'דני',
            'birth_date' => '1998-05-14',
        ]);

        $member = FamilyMember::first();

        $this->assertDatabaseHas('folders', [
            'user_id' => $user->id,
            'calendar_id' => $calendar->id,
            'name' => 'דני',
            'family_member_id' => $member->id,
        ]);
    }

    public function test_renaming_family_member_renames_linked_folder(): void
    {
        $user = User::factory()->create();
        $calendar = $this->createCalendarFor($user);
        $member = $calendar->familyMembers()->create(['name' => 'דני', 'birth_date' => '1998-05-14']);

        $this->actingAs($user)->put("/calendars/{$calendar->id}/members/{$member->id}", ['name' => 'דניאל']);

        $this->assertDatabaseHas('folders', [
            'id' => $member->folder()->first()->id,
            'name' => 'דניאל',
        ]);
    }

    public function test_deleting_family_member_deletes_linked_folder_and_unfiles_media(): void
    {
        [$user, $media] = $this->createUserWithMedia();
        $calendar = $this->createCalendarFor($user);
        $member = $calendar->familyMembers()->create(['name' => 'דני', 'birth_date' => '1998-05-14']);
        $folder = $member->folder()->first();
        $item = $media->first();
        $item->folder_id = $folder->id;
        $item->save();

        $this->actingAs($user)->delete("/calendars/{$calendar->id}/members/{$member->id}");

        $this->assertDatabaseMissing('folders', ['id' => $folder->id]);
        $this->assertDatabaseHas('media', ['id' => $item->id, 'folder_id' => null]);
    }

    public function test_user_can_move_media_into_folder(): void
    {
        [$user, $media] = $this->createUserWithMedia();
        $folder = Folder::create(['user_id' => $user->id, 'name' => 'חופשה']);
        $item = $media->first();

        $response = $this->actingAs($user)->put("/media/{$item->id}/folder", ['folder_id' => $folder->id]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('media', ['id' => $item->id, 'folder_id' => $folder->id]);
    }

    public function test_user_can_move_media_back_to_all_media(): void
    {
        [$user, $media] = $this->createUserWithMedia();
        $folder = Folder::create(['user_id' => $user->id, 'name' => 'חופשה']);
        $item = $media->first();
        $item->folder_id = $folder->id;
        $item->save();

        $this->actingAs($user)->put("/media/{$item->id}/folder", ['folder_id' => '']);

        $this->assertDatabaseHas('media', ['id' => $item->id, 'folder_id' => null]);
    }

    public function test_user_cannot_move_another_users_media(): void
    {
        [$owner, $media] = $this->createUserWithMedia();
        $otherUser = User::factory()->create();

        $this->actingAs($otherUser)
            ->put("/media/{$media->first()->id}/folder", ['folder_id' => ''])
            ->assertForbidden();
    }

    public function test_user_cannot_move_media_into_another_users_folder(): void
    {
        [$owner] = $this->createUserWithMedia();
        $folder = Folder::create(['user_id' => $owner->id, 'name' => 'חופשה']);
        [$otherUser, $otherMedia] = $this->createUserWithMedia(['other.jpg']);

        $this->actingAs($otherUser)
            ->put("/media/{$otherMedia->first()->id}/folder", ['folder_id' => $folder->id])
            ->assertForbidden();

        $this->assertDatabaseHas('media', ['id' => $otherMedia->first()->id, 'folder_id' => null]);
    }

    public function test_user_can_rename_manual_folder(): void
    {
        $user = User::factory()->create();
        $folder = Folder::create(['user_id' => $user->id, 'name' => 'חופשה']);

        $this->actingAs($user)->put("/folders/{$folder->id}", ['name' => 'טיולים']);

        $this->assertDatabaseHas('folders', ['id' => $folder->id, 'name' => 'טיולים']);
    }

    public function test_member_linked_folder_cannot_be_renamed_manually(): void
    {
        $user = User::factory()->create();
        $calendar = $this->createCalendarFor($user);
        $member = $calendar->familyMembers()->create(['name' => 'דני', 'birth_date' => '1998-05-14']);
        $folder = $member->folder()->first();

        $response = $this->actingAs($user)
            ->put("/folders/{$folder->id}", ['name' => 'התיקייה שלי']);

        $response->assertSessionHas('error');

        $this->assertDatabaseHas('folders', ['id' => $folder->id, 'name' => 'דני']);
    }

    public function test_deleting_manual_folder_unfiles_media(): void
    {
        [$user, $media] = $this->createUserWithMedia();
        $folder = Folder::create(['user_id' => $user->id, 'name' => 'חופשה']);
        $item = $media->first();
        $item->folder_id = $folder->id;
        $item->save();

        $this->actingAs($user)->delete("/folders/{$folder->id}");

        $this->assertDatabaseMissing('folders', ['id' => $folder->id]);
        $this->assertDatabaseHas('media', ['id' => $item->id, 'folder_id' => null]);
    }

    public function test_user_cannot_delete_another_users_folder(): void
    {
        $user = User::factory()->create();
        $folder = Folder::create(['user_id' => $user->id, 'name' => 'חופשה']);
        $otherUser = User::factory()->create();

        $this->actingAs($otherUser)->delete("/folders/{$folder->id}")->assertForbidden();

        $this->assertDatabaseHas('folders', ['id' => $folder->id]);
    }

    public function test_member_linked_folder_cannot_be_deleted_manually(): void
    {
        $user = User::factory()->create();
        $calendar = $this->createCalendarFor($user);
        $member = $calendar->familyMembers()->create(['name' => 'דני', 'birth_date' => '1998-05-14']);
        $folder = $member->folder()->first();

        $response = $this->actingAs($user)->delete("/folders/{$folder->id}");

        $response->assertSessionHas('error');

        $this->assertDatabaseHas('folders', ['id' => $folder->id]);
    }

    public function test_media_index_filters_by_folder(): void
    {
        [$user, $media] = $this->createUserWithMedia(['family.jpg', 'dog.png']);
        $folder = Folder::create(['user_id' => $user->id, 'name' => 'חופשה']);
        $media[0]->folder_id = $folder->id;
        $media[0]->save();

        $response = $this->actingAs($user)->get("/media?folder={$folder->id}");

        $response->assertOk();
        $response->assertSee($media[0]->getUrl('thumb'));
        $response->assertDontSee($media[1]->getUrl('thumb'));
    }

    public function test_media_index_does_not_leak_another_users_folder(): void
    {
        [$owner, $media] = $this->createUserWithMedia();
        $folder = Folder::create(['user_id' => $owner->id, 'name' => 'חופשה']);
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)->get("/media?folder={$folder->id}");

        $response->assertOk();
        $response->assertSee('התמונות שלי (0)');
        $response->assertDontSee($media->first()->getUrl('thumb'));
    }

    public function test_uploading_with_folder_id_attaches_media_to_folder(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $folder = Folder::create(['user_id' => $user->id, 'name' => 'חופשה']);

        $this->actingAs($user)->post('/media', [
            'files' => [UploadedFile::fake()->image('trip.jpg')],
            'folder_id' => $folder->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('media', [
            'model_id' => $user->id,
            'folder_id' => $folder->id,
        ]);
    }

    public function test_uploading_while_viewing_folder_attaches_media_to_folder(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $folder = Folder::create(['user_id' => $user->id, 'name' => 'חופשה']);

        $this->actingAs($user)->post('/media?folder='.$folder->id, [
            'files' => [UploadedFile::fake()->image('trip.jpg')],
        ])->assertRedirect();

        $this->assertDatabaseHas('media', [
            'model_id' => $user->id,
            'folder_id' => $folder->id,
        ]);
    }

    public function test_upload_without_folder_stays_unfiled(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->post('/media', [
            'files' => [UploadedFile::fake()->image('trip.jpg')],
        ])->assertRedirect();

        $this->assertDatabaseHas('media', [
            'model_id' => $user->id,
            'folder_id' => null,
        ]);
    }

    public function test_uploading_to_another_users_folder_is_forbidden(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $folder = Folder::create(['user_id' => $owner->id, 'name' => 'חופשה']);
        $otherUser = User::factory()->create();

        $this->actingAs($otherUser)->post('/media', [
            'files' => [UploadedFile::fake()->image('trip.jpg')],
            'folder_id' => $folder->id,
        ])->assertForbidden();

        $this->assertDatabaseCount('media', 0);
    }

    public function test_folders_backfill_command_creates_folders_for_existing_members(): void
    {
        $user = User::factory()->create();
        $calendar = $this->createCalendarFor($user);
        $member = FamilyMember::withoutEvents(fn () => FamilyMember::create([
            'calendar_id' => $calendar->id,
            'name' => 'דני',
            'birth_date' => '1998-05-14',
        ]));

        $this->assertDatabaseMissing('folders', ['family_member_id' => $member->id]);

        $this->artisan('folders:backfill')->assertSuccessful();

        $this->assertDatabaseHas('folders', [
            'user_id' => $user->id,
            'calendar_id' => $calendar->id,
            'name' => 'דני',
            'family_member_id' => $member->id,
        ]);
    }
}
