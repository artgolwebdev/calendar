<?php

namespace Tests\Feature;

use App\Models\CalendarEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CalendarEventsListTest extends TestCase
{
    use RefreshDatabase;

    private function calendarWithAutoAndManualEvents(User $user): array
    {
        $calendar = $user->calendars()->create(['name' => 'לוח משפחתי']);

        $member = $user->familyMembers()->create([
            'name' => 'דני',
            'birth_date' => '1998-05-14',
            'anniversary_date' => '2021-09-03',
        ]);

        $manual = $calendar->calendarEvents()->create([
            'title' => 'מסיבת משפחה',
            'event_date' => '2026-01-20',
            'event_type' => 'custom',
        ]);

        $birthday = CalendarEvent::where('calendar_id', $calendar->id)
            ->where('family_member_id', $member->id)
            ->where('event_type', 'birthday')
            ->firstOrFail();

        $anniversary = CalendarEvent::where('calendar_id', $calendar->id)
            ->where('family_member_id', $member->id)
            ->where('event_type', 'anniversary')
            ->firstOrFail();

        return [$calendar, $member, $manual, $birthday, $anniversary];
    }

    public function test_events_index_lists_auto_generated_and_manual_events(): void
    {
        $user = User::factory()->create();
        [$calendar, $member, $manual, $birthday, $anniversary] = $this->calendarWithAutoAndManualEvents($user);

        $response = $this->actingAs($user)->get(route('calendar-events.index', $calendar));

        $response->assertOk();
        $response->assertSee('יום הולדת - דני');
        $response->assertSee('יום נישואין - דני');
        $response->assertSee('מסיבת משפחה');
        $response->assertSee('chip-birthday', false);
        $response->assertSee('chip-anniversary', false);
        $response->assertSee('chip-event', false);
        $response->assertSee($member->name);
    }

    public function test_events_index_shows_edit_but_no_delete_for_auto_generated_events(): void
    {
        $user = User::factory()->create();
        [$calendar, , $manual, $birthday, $anniversary] = $this->calendarWithAutoAndManualEvents($user);

        $response = $this->actingAs($user)->get(route('calendar-events.index', $calendar));

        $response->assertOk();
        $response->assertSee(route('calendar-events.edit', [$calendar, $manual]));
        $response->assertSee(route('calendar-events.edit', [$calendar, $birthday]));
        $response->assertSee(route('calendar-events.edit', [$calendar, $anniversary]));

        $html = $response->getContent();
        $this->assertStringNotContainsString('action="'.route('calendar-events.destroy', [$calendar, $birthday]).'"', $html);
        $this->assertStringNotContainsString('action="'.route('calendar-events.destroy', [$calendar, $anniversary]).'"', $html);
        $this->assertStringContainsString('action="'.route('calendar-events.destroy', [$calendar, $manual]).'"', $html);
    }

    public function test_auto_generated_event_can_be_partially_edited(): void
    {
        $user = User::factory()->create();
        [$calendar, , , $birthday] = $this->calendarWithAutoAndManualEvents($user);

        $response = $this->actingAs($user)->put(route('calendar-events.update', [$calendar, $birthday]), [
            'title' => 'כותרת חדשה',
            'description' => 'תיאור חדש',
            'event_date' => '2026-01-01',
        ]);

        $response->assertRedirect(route('calendars.show', $calendar));

        $birthday->refresh();
        $this->assertSame('כותרת חדשה', $birthday->title);
        $this->assertSame('תיאור חדש', $birthday->description);
        $this->assertTrue($birthday->title_customized);
        $this->assertTrue($birthday->is_auto_generated);
        $this->assertSame('birthday', $birthday->event_type);
        $this->assertSame('1998-05-14', $birthday->event_date->format('Y-m-d'));
    }

    public function test_auto_generated_event_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        [$calendar, , , $birthday] = $this->calendarWithAutoAndManualEvents($user);

        $response = $this->actingAs($user)->delete(route('calendar-events.destroy', [$calendar, $birthday]));

        $response->assertStatus(403);
        $this->assertDatabaseHas('calendar_events', ['id' => $birthday->id]);
    }

    public function test_manual_event_remains_editable_and_deletable(): void
    {
        $user = User::factory()->create();
        [$calendar, , $manual] = $this->calendarWithAutoAndManualEvents($user);

        $this->actingAs($user)->put(route('calendar-events.update', [$calendar, $manual]), [
            'title' => 'מסיבה משפחתית מעודכנת',
            'event_date' => '2026-01-21',
            'event_type' => 'custom',
        ])->assertRedirect(route('calendars.show', $calendar));

        $this->assertDatabaseHas('calendar_events', [
            'id' => $manual->id,
            'title' => 'מסיבה משפחתית מעודכנת',
        ]);
        $this->assertSame('2026-01-21', $manual->fresh()->event_date->format('Y-m-d'));

        $this->actingAs($user)->delete(route('calendar-events.destroy', [$calendar, $manual]))
            ->assertRedirect(route('calendars.show', $calendar));

        $this->assertDatabaseMissing('calendar_events', ['id' => $manual->id]);
    }

    public function test_events_index_is_scoped_to_the_calendar_owner(): void
    {
        $user = User::factory()->create();
        [$calendar] = $this->calendarWithAutoAndManualEvents($user);

        $other = User::factory()->create();

        $this->actingAs($other)->get(route('calendar-events.index', $calendar))->assertStatus(403);
    }

    public function test_manual_event_can_be_created_with_cover_image_and_description(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $calendar = $user->calendars()->create(['name' => 'לוח משפחתי']);

        $response = $this->actingAs($user)->post(route('calendar-events.store', $calendar), [
            'title' => 'מסיבת סיום',
            'description' => 'מסיבה לכבוד סיום הלימודים',
            'event_date' => '2026-06-30',
            'event_type' => 'custom',
            'cover_image_path' => UploadedFile::fake()->image('cover.jpg'),
        ]);

        $response->assertRedirect(route('calendars.show', $calendar));

        $event = CalendarEvent::firstOrFail();
        $this->assertSame('מסיבה לכבוד סיום הלימודים', $event->description);
        $this->assertStringStartsWith('calendar-covers/', $event->cover_image_path);
        Storage::disk('public')->assertExists($event->cover_image_path);
    }

    public function test_events_index_displays_cover_image_and_type_placeholder(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('calendar-covers/manual.jpg', 'cover content');

        $user = User::factory()->create();
        [$calendar, , $manual, $birthday] = $this->calendarWithAutoAndManualEvents($user);

        $manual->update(['cover_image_path' => 'calendar-covers/manual.jpg']);

        $response = $this->actingAs($user)->get(route('calendar-events.index', $calendar));

        $response->assertOk();
        $response->assertSee('/storage/calendar-covers/manual.jpg');
        $response->assertSee('from-purple-100 to-ink-100', false);
    }

    public function test_create_form_defaults_to_custom_and_hides_system_types(): void
    {
        $user = User::factory()->create();
        $calendar = $user->calendars()->create(['name' => 'לוח משפחתי']);

        $response = $this->actingAs($user)->get(route('calendar-events.create', $calendar));

        $response->assertOk();
        $response->assertSee('value="custom"', false);
        $response->assertDontSee('value="birthday"', false);
        $response->assertDontSee('value="anniversary"', false);
    }

    public function test_customized_title_survives_family_member_sync(): void
    {
        $user = User::factory()->create();
        [$calendar, $member, , $birthday] = $this->calendarWithAutoAndManualEvents($user);

        $this->actingAs($user)->put(route('calendar-events.update', [$calendar, $birthday]), [
            'title' => 'יום ההולדת החדש של דני',
        ])->assertRedirect(route('calendars.show', $calendar));

        $this->actingAs($user)->put("/family-members/{$member->id}", [
            'name' => 'דניאל',
            'birth_date' => '1997-06-20',
            'anniversary_date' => '2021-09-03',
        ])->assertRedirect();

        $birthday->refresh();
        $this->assertSame('יום ההולדת החדש של דני', $birthday->title);
        $this->assertSame('1997-06-20', $birthday->event_date->format('Y-m-d'));
    }

    public function test_edit_form_renders_stored_time_values_without_seconds(): void
    {
        $user = User::factory()->create();
        [$calendar, , $manual] = $this->calendarWithAutoAndManualEvents($user);

        $manual->update(['start_time' => '09:30:00', 'end_time' => '11:45:00']);

        $response = $this->actingAs($user)->get(route('calendar-events.edit', [$calendar, $manual]));

        $response->assertOk();
        $response->assertSee('value="09:30"', false);
        $response->assertSee('value="11:45"', false);
        $response->assertDontSee('value="09:30:00"', false);
        $response->assertDontSee('value="11:45:00"', false);
    }
}
