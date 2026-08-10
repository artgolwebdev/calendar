<?php

namespace Tests\Feature;

use App\Models\Calendar;
use App\Models\FamilyMember;
use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FamilyMemberProfileTest extends TestCase
{
    use RefreshDatabase;

    private function createCalendarFor(User $user): Calendar
    {
        return $user->calendars()->create(['name' => 'לוח משפחתי']);
    }

    /**
     * @return array{0: User, 1: Calendar, 2: FamilyMember, 3: Collection<int, Media>}
     */
    private function createMemberWithMedia(int $count = 1): array
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $calendar = $this->createCalendarFor($user);
        $member = $calendar->familyMembers()->create(['name' => 'דני', 'birth_date' => '1998-05-14']);
        $folder = $member->folder()->first();

        $media = collect();
        for ($i = 0; $i < $count; $i++) {
            $item = $user->addMedia(UploadedFile::fake()->image("family-{$i}.jpg"))->toMediaCollection('user_media');
            $item->folder_id = $folder->id;
            $item->save();
            $media->push($item);
        }

        return [$user, $calendar, $member, $media];
    }

    public function test_guest_cannot_access_family_member_pages(): void
    {
        $this->get('/calendars/1/members')->assertRedirect('/login');
        $this->get('/calendars/1/members/create')->assertRedirect('/login');
        $this->get('/calendars/1/members/1')->assertRedirect('/login');
        $this->get('/calendars/1/members/1/edit')->assertRedirect('/login');
    }

    public function test_create_view_renders_combined_form_with_image_input_and_tag_fields(): void
    {
        $user = User::factory()->create();
        $calendar = $this->createCalendarFor($user);

        $response = $this->actingAs($user)->get("/calendars/{$calendar->id}/members/create");

        $response->assertOk();
        $response->assertSee('הוסף חבר משפחה חדש');
        $response->assertSee('חזרה לעריכת לוח השנה');
        $response->assertSee('memberImages', false);
        $response->assertSee('בחירת קבצים');
        $response->assertSee('תחביבים');
        $response->assertSee('ספורט אהוב');
        $response->assertSee('מוזיקה אהובה');
        $response->assertSee('אוכל אהוב');
    }

    public function test_edit_view_renders_same_combined_form_with_gallery(): void
    {
        [$user, $calendar, $member] = $this->createMemberWithMedia();

        $response = $this->actingAs($user)->get("/calendars/{$calendar->id}/members/{$member->id}/edit");

        $response->assertOk();
        $response->assertSee($member->name);
        $response->assertSee('שמירת שינויים');
        $response->assertSee('תמונות');
        $response->assertSee('memberImages', false);
        $response->assertSee('פתיחת התיקייה בספרייה');
    }

    public function test_show_renders_same_combined_form_as_edit(): void
    {
        [$user, $calendar, $member, $media] = $this->createMemberWithMedia();
        $folder = $member->folder()->first();

        $response = $this->actingAs($user)->get("/calendars/{$calendar->id}/members/{$member->id}");

        $response->assertOk();
        $response->assertSee($member->name);
        $response->assertSee($media->first()->getUrl('thumb'));
        $response->assertSee('memberImages', false);
        $response->assertSee('בחירת קבצים');
        $response->assertSee('שמירת שינויים');
        $response->assertSee('פתיחת התיקייה בספרייה');
        $response->assertSee(route('media.index', ['folder' => $folder->id]));
    }

    public function test_show_displays_empty_gallery_state(): void
    {
        $user = User::factory()->create();
        $calendar = $this->createCalendarFor($user);
        $member = $calendar->familyMembers()->create(['name' => 'דני', 'birth_date' => '1998-05-14']);

        $response = $this->actingAs($user)->get("/calendars/{$calendar->id}/members/{$member->id}");

        $response->assertOk();
        $response->assertSee('אין עדיין תמונות');
    }

    public function test_show_is_forbidden_for_another_user(): void
    {
        [$owner, $calendar] = $this->createMemberWithMedia();
        $member = $calendar->familyMembers()->first();
        $otherUser = User::factory()->create();

        $this->actingAs($otherUser)->get("/calendars/{$calendar->id}/members/{$member->id}")->assertForbidden();
    }

    public function test_index_shows_image_count_badge_per_member(): void
    {
        [$user, $calendar] = $this->createMemberWithMedia(count: 3);

        $response = $this->actingAs($user)->get("/calendars/{$calendar->id}/members");

        $response->assertOk();
        $response->assertSee('דני');
        $response->assertSee('data-media-count="3"', false);
    }

    public function test_index_shows_zero_count_badge_for_member_without_images(): void
    {
        $user = User::factory()->create();
        $calendar = $this->createCalendarFor($user);
        $calendar->familyMembers()->create(['name' => 'מאיה', 'birth_date' => '2000-01-01']);

        $response = $this->actingAs($user)->get("/calendars/{$calendar->id}/members");

        $response->assertOk();
        $response->assertSee('data-media-count="0"', false);
    }

    public function test_creating_member_with_images_attaches_them_to_the_member_folder(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $calendar = $this->createCalendarFor($user);

        $response = $this->actingAs($user)->post("/calendars/{$calendar->id}/members", [
            'name' => 'דני',
            'birth_date' => '1998-05-14',
            'images' => [
                UploadedFile::fake()->image('portrait.jpg'),
                UploadedFile::fake()->image('hobby.png'),
            ],
        ]);

        $response->assertRedirect(route('calendars.edit', $calendar));

        $member = FamilyMember::where('calendar_id', $calendar->id)->first();
        $this->assertNotNull($member);

        $folderId = $member->folder()->first()->id;

        $this->assertDatabaseHas('media', ['model_id' => $user->id, 'folder_id' => $folderId]);
        $this->assertSame(2, $user->getMedia('user_media')->where('folder_id', $folderId)->count());
    }

    public function test_updating_member_with_images_attaches_them_to_the_member_folder(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $calendar = $this->createCalendarFor($user);
        $member = $calendar->familyMembers()->create(['name' => 'דני', 'birth_date' => '1998-05-14']);
        $folderId = $member->folder()->first()->id;

        $response = $this->actingAs($user)->put("/calendars/{$calendar->id}/members/{$member->id}", [
            'name' => 'דניאל',
            'birth_date' => '1998-05-14',
            'images' => [UploadedFile::fake()->image('portrait.jpg')],
        ]);

        $response->assertRedirect(route('calendars.edit', $calendar));

        $this->assertDatabaseHas('media', ['model_id' => $user->id, 'folder_id' => $folderId]);
        $this->assertDatabaseHas('family_members', ['id' => $member->id, 'name' => 'דניאל']);
    }

    public function test_creating_member_without_images_still_works(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $calendar = $this->createCalendarFor($user);

        $this->actingAs($user)->post("/calendars/{$calendar->id}/members", [
            'name' => 'מאיה',
            'birth_date' => '2000-01-01',
        ])->assertRedirect(route('calendars.edit', $calendar));

        $this->assertSame(1, FamilyMember::where('calendar_id', $calendar->id)->count());
        $this->assertSame(0, $user->getMedia('user_media')->count());
    }

    public function test_creating_member_with_tag_fields_persists_arrays(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $calendar = $this->createCalendarFor($user);

        $this->actingAs($user)->post("/calendars/{$calendar->id}/members", [
            'name' => 'דני',
            'birth_date' => '1998-05-14',
            'hobbies' => ['שחייה', 'ציור'],
            'favorite_sports' => ['כדורגל'],
            'favorite_music' => ['רוק'],
            'favorite_food' => ['פיצה', 'שקשוקה'],
        ])->assertRedirect(route('calendars.edit', $calendar));

        $member = FamilyMember::where('calendar_id', $calendar->id)->firstOrFail();
        $this->assertSame(['שחייה', 'ציור'], $member->hobbies);
        $this->assertSame(['כדורגל'], $member->favorite_sports);
        $this->assertSame(['רוק'], $member->favorite_music);
        $this->assertSame(['פיצה', 'שקשוקה'], $member->favorite_food);
    }

    public function test_uploading_to_member_folder_attaches_media_to_that_folder(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $calendar = $this->createCalendarFor($user);
        $member = $calendar->familyMembers()->create(['name' => 'דני', 'birth_date' => '1998-05-14']);
        $folder = $member->folder()->first();

        $this->actingAs($user)->post('/media', [
            'files' => [UploadedFile::fake()->image('member.jpg')],
            'folder_id' => $folder->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('media', [
            'model_id' => $user->id,
            'folder_id' => $folder->id,
        ]);
    }
}
