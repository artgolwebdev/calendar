<?php

namespace Tests\Feature;

use App\Models\Calendar;
use App\Models\FamilyMember;
use App\Models\MonthPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MemberBirthMonthBackgroundTest extends TestCase
{
    use RefreshDatabase;

    private function createCalendarWithMonthPages(User $user): Calendar
    {
        $calendar = $user->calendars()->create(['name' => 'לוח משפחתי']);

        foreach (range(1, 12) as $monthNumber) {
            $calendar->monthPages()->create([
                'month_number' => $monthNumber,
                'font_choice' => 'default',
                'overlay_opacity' => 30,
                'day_box_bg_color' => '#FFFFFF',
                'day_box_font_color' => '#2B2E3A',
                'day_box_bg_opacity' => 100,
                'show_adjacent_month_days' => true,
            ]);
        }

        return $calendar;
    }

    private function birthMonthPage(Calendar $calendar, int $monthNumber): MonthPage
    {
        return $calendar->monthPages()->where('month_number', $monthNumber)->firstOrFail();
    }

    public function test_wizard_member_photo_becomes_birth_month_background(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $this->actingAs($user)->postJson(route('calendars.wizard.store'), [
            'name' => 'לוח משפחתי',
            'members' => [
                [
                    'name' => 'דנה כהן',
                    'birth_date' => '1990-05-12',
                    'image' => UploadedFile::fake()->image('member.jpg'),
                ],
            ],
        ])->assertOk();

        $member = FamilyMember::first();
        $calendar = $member->calendar;
        $photo = $member->folder->media()->first();

        $this->assertDatabaseHas('month_pages', [
            'id' => $this->birthMonthPage($calendar, 5)->id,
            'auto_background_media_id' => $photo->id,
            'auto_background_family_member_id' => $member->id,
        ]);
    }

    public function test_member_form_photo_becomes_birth_month_background(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $calendar = $this->createCalendarWithMonthPages($user);

        $this->actingAs($user)->post(
            "/calendars/{$calendar->id}/members",
            [
                'name' => 'יוסי כהן',
                'birth_date' => '1988-11-03',
                'images' => [UploadedFile::fake()->image('member.jpg')],
            ]
        );

        $member = FamilyMember::first();
        $photo = $member->folder->media()->first();

        $this->assertDatabaseHas('month_pages', [
            'id' => $this->birthMonthPage($calendar, 11)->id,
            'auto_background_media_id' => $photo->id,
            'auto_background_family_member_id' => $member->id,
        ]);
    }

    public function test_first_photo_wins_for_members_sharing_a_birth_month(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $this->actingAs($user)->postJson(route('calendars.wizard.store'), [
            'name' => 'לוח משפחתי',
            'members' => [
                [
                    'name' => 'אבא',
                    'birth_date' => '1980-05-01',
                    'image' => UploadedFile::fake()->image('first.jpg'),
                ],
                [
                    'name' => 'אמא',
                    'birth_date' => '1982-05-14',
                    'image' => UploadedFile::fake()->image('second.jpg'),
                ],
            ],
        ])->assertOk();

        $first = FamilyMember::where('name', 'אבא')->first();
        $second = FamilyMember::where('name', 'אמא')->first();
        $page = $this->birthMonthPage($first->calendar, 5);

        $this->assertDatabaseHas('month_pages', [
            'id' => $page->id,
            'auto_background_media_id' => $first->folder->media()->first()->id,
            'auto_background_family_member_id' => $first->id,
        ]);
        $this->assertNotSame($second->folder->media()->first()->id, $page->fresh()->auto_background_media_id);
    }

    public function test_new_photo_for_same_member_replaces_their_own_background(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $calendar = $this->createCalendarWithMonthPages($user);

        $this->actingAs($user)->post(
            "/calendars/{$calendar->id}/members",
            [
                'name' => 'דנה כהן',
                'birth_date' => '1990-05-12',
                'images' => [UploadedFile::fake()->image('first.jpg')],
            ]
        );

        $member = FamilyMember::first();
        $firstPhoto = $member->folder->media()->orderByDesc('id')->first();

        $this->actingAs($user)->put(
            "/calendars/{$calendar->id}/members/{$member->id}",
            [
                'name' => 'דנה כהן',
                'birth_date' => '1990-05-12',
                'images' => [UploadedFile::fake()->image('second.jpg')],
            ]
        );

        $secondPhoto = $member->folder->media()->orderByDesc('id')->first();

        $this->assertNotSame($firstPhoto->id, $secondPhoto->id);
        $this->assertDatabaseHas('month_pages', [
            'id' => $this->birthMonthPage($calendar, 5)->id,
            'auto_background_media_id' => $secondPhoto->id,
        ]);
    }

    public function test_member_photo_does_not_override_manual_background(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $calendar = $this->createCalendarWithMonthPages($user);
        $manual = $user->addMedia(UploadedFile::fake()->image('manual.jpg'))->toMediaCollection('user_media');

        $this->birthMonthPage($calendar, 5)->update(['background_media_id' => $manual->id]);

        $this->actingAs($user)->post(
            "/calendars/{$calendar->id}/members",
            [
                'name' => 'דנה כהן',
                'birth_date' => '1990-05-12',
                'images' => [UploadedFile::fake()->image('member.jpg')],
            ]
        );

        $this->assertDatabaseHas('month_pages', [
            'id' => $this->birthMonthPage($calendar, 5)->id,
            'background_media_id' => $manual->id,
            'auto_background_media_id' => null,
            'auto_background_family_member_id' => null,
        ]);
    }

    public function test_deleting_member_photo_clears_auto_background(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $calendar = $this->createCalendarWithMonthPages($user);

        $this->actingAs($user)->post(
            "/calendars/{$calendar->id}/members",
            [
                'name' => 'דנה כהן',
                'birth_date' => '1990-05-12',
                'images' => [UploadedFile::fake()->image('member.jpg')],
            ]
        );

        $member = FamilyMember::first();
        $photo = $member->folder->media()->first();

        $this->actingAs($user)->delete("/media/{$photo->id}");

        $this->assertDatabaseHas('month_pages', [
            'id' => $this->birthMonthPage($calendar, 5)->id,
            'auto_background_media_id' => null,
            'auto_background_family_member_id' => null,
        ]);
    }

    public function test_deleting_member_clears_auto_background(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $calendar = $this->createCalendarWithMonthPages($user);

        $this->actingAs($user)->post(
            "/calendars/{$calendar->id}/members",
            [
                'name' => 'דנה כהן',
                'birth_date' => '1990-05-12',
                'images' => [UploadedFile::fake()->image('member.jpg')],
            ]
        );

        $member = FamilyMember::first();

        $this->actingAs($user)->delete("/calendars/{$calendar->id}/members/{$member->id}");

        $this->assertDatabaseHas('month_pages', [
            'id' => $this->birthMonthPage($calendar, 5)->id,
            'auto_background_media_id' => null,
            'auto_background_family_member_id' => null,
        ]);
    }

    public function test_changing_birth_month_moves_auto_background(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $calendar = $this->createCalendarWithMonthPages($user);

        $this->actingAs($user)->post(
            "/calendars/{$calendar->id}/members",
            [
                'name' => 'דנה כהן',
                'birth_date' => '1990-05-12',
                'images' => [UploadedFile::fake()->image('member.jpg')],
            ]
        );

        $member = FamilyMember::first();

        $this->actingAs($user)->put(
            "/calendars/{$calendar->id}/members/{$member->id}",
            [
                'name' => 'דנה כהן',
                'birth_date' => '1990-09-12',
            ]
        );

        $photo = $member->folder->media()->first();

        $this->assertDatabaseHas('month_pages', [
            'id' => $this->birthMonthPage($calendar, 9)->id,
            'auto_background_media_id' => $photo->id,
            'auto_background_family_member_id' => $member->id,
        ]);
        $this->assertDatabaseHas('month_pages', [
            'id' => $this->birthMonthPage($calendar, 5)->id,
            'auto_background_media_id' => null,
            'auto_background_family_member_id' => null,
        ]);
    }

    public function test_selecting_manual_background_clears_auto_background(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $calendar = $this->createCalendarWithMonthPages($user);

        $this->actingAs($user)->post(
            "/calendars/{$calendar->id}/members",
            [
                'name' => 'דנה כהן',
                'birth_date' => '1990-05-12',
                'images' => [UploadedFile::fake()->image('member.jpg')],
            ]
        );

        $member = FamilyMember::first();
        $manual = $user->addMedia(UploadedFile::fake()->image('manual.jpg'))->toMediaCollection('user_media');
        $page = $this->birthMonthPage($calendar, 5);

        $this->actingAs($user)->put(
            "/calendars/{$calendar->id}/month-pages/{$page->id}",
            ['background_media_id' => $manual->id]
        );

        $this->assertDatabaseHas('month_pages', [
            'id' => $page->id,
            'background_media_id' => $manual->id,
            'auto_background_media_id' => null,
            'auto_background_family_member_id' => null,
        ]);
    }

    public function test_month_view_and_design_panel_reflect_auto_background(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $calendar = $this->createCalendarWithMonthPages($user);

        $this->actingAs($user)->post(
            "/calendars/{$calendar->id}/members",
            [
                'name' => 'דנה כהן',
                'birth_date' => '1990-05-12',
                'images' => [UploadedFile::fake()->image('member.jpg')],
            ]
        );

        $member = FamilyMember::first();
        $photo = $member->folder->media()->first();

        $response = $this->actingAs($user)->get("/calendars/{$calendar->id}/month/5/".now()->year);

        $response->assertOk();
        $response->assertSee($photo->getUrl());
        $response->assertSee('הוגדר אוטומטית מתמונת דנה כהן (חודש יומולדת)');
    }
}
