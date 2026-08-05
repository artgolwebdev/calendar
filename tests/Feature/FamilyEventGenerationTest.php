<?php

namespace Tests\Feature;

use App\Models\Calendar;
use App\Models\CalendarEvent;
use App\Models\FamilyMember;
use App\Models\User;
use App\Services\MonthPageStyleService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FamilyEventGenerationTest extends TestCase
{
    use RefreshDatabase;

    private function createCalendarFor(User $user): Calendar
    {
        $calendar = $user->calendars()->create(['name' => 'לוח משפחתי']);

        for ($month = 1; $month <= 12; $month++) {
            $calendar->monthPages()->create([
                'month_number' => $month,
                ...app(MonthPageStyleService::class)->defaults(),
            ]);
        }

        return $calendar;
    }

    public function test_creating_family_member_generates_auto_events_on_all_calendars(): void
    {
        $user = User::factory()->create();
        $this->createCalendarFor($user);
        $this->createCalendarFor($user);

        $response = $this->actingAs($user)->post('/family-members', [
            'name' => 'דני',
            'birth_date' => '1998-05-14',
            'anniversary_date' => '2021-09-03',
        ]);

        $response->assertRedirect();

        $member = FamilyMember::first();
        $this->assertCount(4, CalendarEvent::where('family_member_id', $member->id)->get());

        foreach ($user->calendars as $calendar) {
            $birthday = CalendarEvent::where('calendar_id', $calendar->id)
                ->where('family_member_id', $member->id)
                ->where('event_type', 'birthday')
                ->first();
            $this->assertNotNull($birthday);
            $this->assertTrue((bool) $birthday->is_auto_generated);
            $this->assertSame('1998-05-14', $birthday->event_date->format('Y-m-d'));
            $this->assertSame('יום הולדת - דני', $birthday->title);

            $anniversary = CalendarEvent::where('calendar_id', $calendar->id)
                ->where('family_member_id', $member->id)
                ->where('event_type', 'anniversary')
                ->first();
            $this->assertNotNull($anniversary);
            $this->assertTrue((bool) $anniversary->is_auto_generated);
            $this->assertSame('2021-09-03', $anniversary->event_date->format('Y-m-d'));
            $this->assertSame('יום נישואין - דני', $anniversary->title);
        }
    }

    public function test_member_without_anniversary_only_generates_birthday_events(): void
    {
        $user = User::factory()->create();
        $calendar = $this->createCalendarFor($user);

        $this->actingAs($user)->post('/family-members', [
            'name' => 'מאיה',
            'birth_date' => '2000-01-01',
        ]);

        $member = FamilyMember::first();
        $this->assertCount(1, $calendar->calendarEvents()->where('family_member_id', $member->id)->get());
        $this->assertDatabaseMissing('calendar_events', [
            'family_member_id' => $member->id,
            'event_type' => 'anniversary',
        ]);
    }

    public function test_updating_member_syncs_auto_event_date_and_title_without_duplicates(): void
    {
        $user = User::factory()->create();
        $this->createCalendarFor($user);

        $member = $user->familyMembers()->create([
            'name' => 'דני',
            'birth_date' => '1998-05-14',
        ]);

        $this->actingAs($user)->put("/family-members/{$member->id}", [
            'name' => 'דניאל',
            'birth_date' => '1997-06-20',
            'anniversary_date' => '2021-09-03',
        ]);

        $calendar = $user->calendars->first();
        $birthday = CalendarEvent::where('calendar_id', $calendar->id)
            ->where('family_member_id', $member->id)
            ->where('event_type', 'birthday')
            ->first();
        $this->assertNotNull($birthday);
        $this->assertSame('1997-06-20', $birthday->event_date->format('Y-m-d'));
        $this->assertSame('יום הולדת - דניאל', $birthday->title);

        $anniversary = CalendarEvent::where('calendar_id', $calendar->id)
            ->where('family_member_id', $member->id)
            ->where('event_type', 'anniversary')
            ->first();
        $this->assertNotNull($anniversary);
        $this->assertSame('2021-09-03', $anniversary->event_date->format('Y-m-d'));
        $this->assertSame('יום נישואין - דניאל', $anniversary->title);

        $this->assertSame(1, CalendarEvent::where('family_member_id', $member->id)
            ->where('event_type', 'birthday')
            ->where('is_auto_generated', true)
            ->count());
    }

    public function test_deleting_member_removes_auto_events(): void
    {
        $user = User::factory()->create();
        $calendar = $this->createCalendarFor($user);

        $member = $user->familyMembers()->create([
            'name' => 'דני',
            'birth_date' => '1998-05-14',
            'anniversary_date' => '2021-09-03',
        ]);

        $this->actingAs($user)->delete("/family-members/{$member->id}");

        $this->assertSame(0, CalendarEvent::where('family_member_id', $member->id)->count());
        $this->assertSame(0, $calendar->calendarEvents()->where('is_auto_generated', true)->count());
    }

    public function test_month_view_shows_auto_event_in_the_correct_month_with_age(): void
    {
        Http::fake(['https://www.hebcal.com/*' => Http::response(['items' => []])]);

        $user = User::factory()->create();
        $calendar = $this->createCalendarFor($user);

        $user->familyMembers()->create([
            'name' => 'דני',
            'birth_date' => '1998-05-14',
            'anniversary_date' => '2021-09-03',
        ]);

        $response = $this->actingAs($user)->get("/calendars/{$calendar->id}/month/5/2026");
        $response->assertOk();
        $response->assertSee('יום הולדת - דני (28)');

        $otherMonth = $this->actingAs($user)->get("/calendars/{$calendar->id}/month/4/2026");
        $otherMonth->assertOk();
        $otherMonth->assertDontSee('יום הולדת - דני (28)');
    }

    public function test_month_view_resolves_auto_events_against_the_displayed_year(): void
    {
        Http::fake(['https://www.hebcal.com/*' => Http::response(['items' => []])]);

        $user = User::factory()->create();
        $calendar = $this->createCalendarFor($user);

        $user->familyMembers()->create([
            'name' => 'דני',
            'birth_date' => '1998-05-14',
        ]);

        $response = $this->actingAs($user)->get("/calendars/{$calendar->id}/month/5/2027");
        $response->assertOk();
        $response->assertSee('יום הולדת - דני (29)');
    }

    public function test_february_29_birthday_renders_on_february_28_in_a_non_leap_year(): void
    {
        Http::fake(['https://www.hebcal.com/*' => Http::response(['items' => []])]);

        $user = User::factory()->create();
        $calendar = $this->createCalendarFor($user);

        $user->familyMembers()->create([
            'name' => 'יובל',
            'birth_date' => '2000-02-29',
        ]);

        $response = $this->actingAs($user)->get("/calendars/{$calendar->id}/month/2/2026");
        $response->assertOk();
        $response->assertSee('יום הולדת - יובל (26)');
    }

    public function test_yearly_view_includes_auto_events_in_the_correct_month_tile(): void
    {
        Http::fake(['https://www.hebcal.com/*' => Http::response(['items' => []])]);

        Carbon::setTestNow(Carbon::parse('2026-01-15'));

        $user = User::factory()->create();
        $calendar = $this->createCalendarFor($user);

        $user->familyMembers()->create([
            'name' => 'דני',
            'birth_date' => '1998-05-14',
            'anniversary_date' => '2021-09-03',
        ]);

        $response = $this->actingAs($user)->get(route('calendars.show', $calendar));

        $response->assertOk();
        $response->assertSee('יום הולדת - דני (28)');
        $response->assertSee('יום נישואין - דני (5 שנים)');

        Carbon::setTestNow();
    }
}
