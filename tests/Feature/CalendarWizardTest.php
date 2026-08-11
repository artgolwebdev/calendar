<?php

namespace Tests\Feature;

use App\Models\Calendar;
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
}
