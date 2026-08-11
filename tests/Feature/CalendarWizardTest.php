<?php

namespace Tests\Feature;

use App\Models\Calendar;
use App\Models\CalendarEvent;
use App\Models\FamilyMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CalendarWizardTest extends TestCase
{
    use RefreshDatabase;

    public function test_wizard_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('calendars.wizard'));

        $response->assertOk();
        $response->assertSee('צור לוח שנה חדש');
        $response->assertSee('calendarWizard', false);
        $response->assertSee('previewGrid', false);
        $response->assertSee('ערכת צבעים');
    }

    public function test_wizard_member_form_includes_optional_photo_upload(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('calendars.wizard'));

        $response->assertOk();

        $html = $response->getContent();

        $this->assertStringContainsString('x-ref="memberImageInput"', $html);
        $this->assertStringContainsString('בחירת תמונת פרופיל', $html);
        $this->assertStringContainsString('onMemberImage($event)', $html);
        $this->assertStringContainsString('onMemberImageDrop($event)', $html);
        $this->assertStringContainsString('memberImageDragOver', $html);
        $this->assertStringContainsString('clearMemberImage()', $html);
        $this->assertStringContainsString('בחירת קובץ', $html);
        $this->assertStringContainsString('accept="image/jpeg,image/png,image/webp"', $html);
    }

    public function test_wizard_creates_a_calendar_without_members(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('calendars.wizard.store'), [
            'name' => 'לוח משפחתי',
        ]);

        $response->assertOk();
        $response->assertJsonPath('calendar_id', Calendar::first()->id);
        $response->assertJsonPath('members', 0);

        $this->assertDatabaseHas('calendars', [
            'user_id' => $user->id,
            'name' => 'לוח משפחתי',
        ]);

        $calendar = Calendar::first();
        $this->assertCount(12, $calendar->monthPages);
        $this->assertCount(0, $calendar->familyMembers);
    }

    public function test_wizard_creates_a_calendar_with_a_cover_image(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('calendars.wizard.store'), [
            'name' => 'לוח משפחתי',
            'cover_image_path' => UploadedFile::fake()->image('cover.jpg'),
        ]);

        $response->assertOk();

        $calendar = Calendar::first();
        $this->assertNotNull($calendar->cover_image_path);
        Storage::disk('public')->assertExists($calendar->cover_image_path);
        $this->assertStringStartsWith('calendar-covers/', $calendar->cover_image_path);
    }

    public function test_wizard_creates_family_members_scoped_to_the_calendar(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('calendars.wizard.store'), [
            'name' => 'לוח משפחתי',
            'members' => [
                [
                    'name' => 'דנה כהן',
                    'birth_date' => '1990-05-12',
                    'hobbies' => ['שחייה', 'ציור'],
                    'favorite_sports' => ['טניס'],
                ],
                [
                    'name' => 'יוסי כהן',
                    'birth_date' => '1988-11-03',
                    'favorite_food' => ['פיצה'],
                ],
            ],
        ]);

        $response->assertOk();
        $response->assertJsonPath('members', 2);

        $calendar = Calendar::first();
        $this->assertCount(2, $calendar->familyMembers);

        $this->assertEqualsCanonicalizing(
            ['שחייה', 'ציור'],
            FamilyMember::where('calendar_id', $calendar->id)->where('name', 'דנה כהן')->first()->hobbies
        );
    }

    public function test_wizard_creates_a_member_folder_for_images(): void
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
        ]);

        $member = FamilyMember::first();
        $folder = $member->folder;
        $this->assertNotNull($folder);
        $this->assertSame($member->calendar_id, $folder->calendar_id);
        $this->assertSame(1, $member->folder->media()->count());
        $this->assertSame(1, $user->getMedia('user_media')->where('folder_id', $folder->id)->count());
    }

    public function test_wizard_requires_a_calendar_name(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('calendars.wizard.store'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('name');
        $this->assertDatabaseCount('calendars', 0);
    }

    public function test_wizard_validates_member_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('calendars.wizard.store'), [
            'name' => 'לוח משפחתי',
            'members' => [
                ['name' => ''],
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('members.0.name');
        $this->assertDatabaseCount('calendars', 0);
        $this->assertDatabaseCount('family_members', 0);
    }

    public function test_wizard_auto_generates_birthday_and_anniversary_events(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('calendars.wizard.store'), [
            'name' => 'לוח משפחתי',
            'members' => [
                [
                    'key' => 'member-a',
                    'name' => 'דנה כהן',
                    'birth_date' => '1990-05-12',
                    'anniversary_date' => '2015-06-01',
                ],
            ],
        ]);

        $response->assertOk();

        $calendar = Calendar::first();
        $events = $calendar->calendarEvents;
        $this->assertCount(2, $events);
        $this->assertTrue($events->every(fn ($event) => $event->is_auto_generated));
        $this->assertSame('יום הולדת - דנה כהן', $events->firstWhere('event_type', 'birthday')->title);
        $this->assertSame('יום נישואין - דנה כהן', $events->firstWhere('event_type', 'anniversary')->title);
    }

    public function test_wizard_creates_manual_events_scoped_to_the_calendar(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('calendars.wizard.store'), [
            'name' => 'לוח משפחתי',
            'members' => [
                [
                    'key' => 'member-a',
                    'name' => 'דנה כהן',
                    'birth_date' => '1990-05-12',
                ],
            ],
            'events' => [
                [
                    'title' => 'טיול משפחתי',
                    'description' => 'נופש בכנרת',
                    'event_date' => '2026-08-20',
                    'start_time' => '10:00',
                    'end_time' => '16:00',
                    'family_member_key' => 'member-a',
                ],
                [
                    'title' => 'פיקניק',
                    'event_date' => '2026-09-01',
                ],
            ],
        ]);

        $response->assertOk();

        $calendar = Calendar::first();
        $member = $calendar->familyMembers()->first();

        $this->assertDatabaseHas('calendar_events', [
            'calendar_id' => $calendar->id,
            'family_member_id' => $member->id,
            'title' => 'טיול משפחתי',
            'description' => 'נופש בכנרת',
            'event_type' => 'custom',
        ]);

        $events = $calendar->calendarEvents;
        $this->assertCount(3, $events);

        $attached = $events->where('title', 'טיול משפחתי')->first();
        $this->assertFalse($attached->is_auto_generated);
        $this->assertSame('2026-08-20', $attached->event_date->format('Y-m-d'));
        $this->assertSame('10:00', $attached->start_time);
        $this->assertSame('16:00', $attached->end_time);

        $manual = $events->where('is_auto_generated', false);
        $this->assertCount(2, $manual);
        $this->assertSame('פיקניק', $manual->last()->title);
        $this->assertNull($manual->last()->family_member_id);
    }

    public function test_wizard_stores_manual_event_cover_image(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $this->actingAs($user)->postJson(route('calendars.wizard.store'), [
            'name' => 'לוח משפחתי',
            'events' => [
                [
                    'title' => 'טיול משפחתי',
                    'event_date' => '2026-08-20',
                    'cover_image_path' => UploadedFile::fake()->image('event-cover.jpg'),
                ],
            ],
        ]);

        $event = CalendarEvent::first();
        $this->assertNotNull($event->cover_image_path);
        Storage::disk('public')->assertExists($event->cover_image_path);
        $this->assertStringStartsWith('calendar-covers/', $event->cover_image_path);
    }

    public function test_wizard_validates_event_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('calendars.wizard.store'), [
            'name' => 'לוח משפחתי',
            'events' => [
                ['title' => ''],
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('events.0.title');
        $this->assertDatabaseCount('calendars', 0);
    }

    public function test_wizard_event_member_key_must_reference_a_submitted_member(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('calendars.wizard.store'), [
            'name' => 'לוח משפחתי',
            'events' => [
                [
                    'title' => 'טיול משפחתי',
                    'event_date' => '2026-08-20',
                    'family_member_key' => 'unknown-member',
                ],
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('events.0.family_member_key');
        $this->assertDatabaseCount('calendars', 0);
    }

    public function test_wizard_applies_selected_theme_to_all_month_pages(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('calendars.wizard.store'), [
            'name' => 'לוח משפחתי',
            'theme' => 'greenish',
        ]);

        $response->assertOk();

        $calendar = Calendar::first();
        $this->assertCount(12, $calendar->monthPages);
        $this->assertTrue($calendar->monthPages->every(
            fn ($page) => $page->font_choice === 'modern'
                && $page->day_box_bg_color === '#DCFCE7'
                && $page->day_box_font_color === '#14532D'
                && $page->overlay_opacity === 20
        ));
    }

    public function test_wizard_without_theme_keeps_default_month_page_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson(route('calendars.wizard.store'), [
            'name' => 'לוח משפחתי',
        ])->assertOk();

        $calendar = Calendar::first();
        $this->assertTrue($calendar->monthPages->every(fn ($page) => $page->font_choice === 'default'));
        $this->assertSame('#FFFFFF', $calendar->monthPages->first()->day_box_bg_color);
    }

    public function test_wizard_rejects_unknown_theme(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('calendars.wizard.store'), [
            'name' => 'לוח משפחתי',
            'theme' => 'not-a-theme',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('theme');
        $this->assertDatabaseCount('calendars', 0);
    }
}
