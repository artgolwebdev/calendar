<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DayViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_day_view_requires_authentication(): void
    {
        $response = $this->get('/calendars/1/day/2026-03-15');

        $response->assertRedirect(route('login'));
    }

    public function test_day_view_prevents_access_to_other_users_calendars(): void
    {
        $user = User::factory()->create();
        $owner = User::factory()->create();
        $calendar = $owner->calendars()->create(['name' => 'לוח של מישהו אחר']);

        $response = $this->actingAs($user)->get(route('calendars.day', [$calendar, '2026-03-15']));

        $response->assertForbidden();
    }

    public function test_day_view_returns_404_for_an_invalid_date(): void
    {
        $user = User::factory()->create();
        $calendar = $user->calendars()->create(['name' => 'לוח משפחתי']);

        $response = $this->actingAs($user)->get(route('calendars.day', [$calendar, 'not-a-date']));

        $response->assertNotFound();
    }

    public function test_day_view_renders_hour_rows_and_positioned_events(): void
    {
        Http::fake(['https://www.hebcal.com/*' => Http::response(['items' => []])]);

        $user = User::factory()->create();
        $calendar = $user->calendars()->create(['name' => 'לוח משפחתי']);
        $calendar->calendarEvents()->create([
            'title' => 'פגישת עבודה',
            'event_date' => '2026-03-15',
            'event_type' => 'custom',
            'start_time' => '09:00',
            'end_time' => '10:30',
        ]);
        $calendar->calendarEvents()->create([
            'title' => 'הולדת סבא',
            'event_date' => '2026-03-15',
            'event_type' => 'birthday',
        ]);

        $response = $this->actingAs($user)->get(route('calendars.day', [$calendar, '2026-03-15']));

        $response->assertOk();
        $response->assertSee('09:00');
        $response->assertSee('פגישת עבודה');
        $response->assertSee('top: 37.5%', false);
        $response->assertSee('height: 6.25%', false);
        $response->assertSee('width: 100%;', false);
        $response->assertSee('הולדת סבא');
        $response->assertSee('chip-birthday', false);
    }

    public function test_day_view_shows_now_line_only_for_today(): void
    {
        Http::fake(['https://www.hebcal.com/*' => Http::response(['items' => []])]);

        Carbon::setTestNow(Carbon::parse('2026-03-15T12:30:00'));

        $user = User::factory()->create();
        $calendar = $user->calendars()->create(['name' => 'לוח משפחתי']);

        $today = $this->actingAs($user)->get(route('calendars.day', [$calendar, '2026-03-15']));
        $today->assertOk();
        $today->assertSee('dayViewNowLine', false);

        $tomorrow = $this->actingAs($user)->get(route('calendars.day', [$calendar, '2026-03-16']));
        $tomorrow->assertOk();
        $tomorrow->assertDontSee('dayViewNowLine', false);

        Carbon::setTestNow();
    }

    public function test_day_view_shows_holidays(): void
    {
        Http::fake([
            'https://www.hebcal.com/*' => Http::response(['items' => [
                ['title' => 'פורים', 'date' => '2026-03-23', 'category' => 'holiday'],
            ]]),
        ]);

        Carbon::setTestNow(Carbon::parse('2026-03-15'));

        $user = User::factory()->create();
        $calendar = $user->calendars()->create(['name' => 'לוח משפחתי']);

        $response = $this->actingAs($user)->get(route('calendars.day', [$calendar, '2026-03-23']));

        $response->assertOk();
        $response->assertSee('פורים');
        $response->assertSee('chip-holiday', false);

        Carbon::setTestNow();
    }

    public function test_event_store_accepts_start_and_end_times(): void
    {
        $user = User::factory()->create();
        $calendar = $user->calendars()->create(['name' => 'לוח משפחתי']);

        $response = $this->actingAs($user)->post(route('calendar-events.store', $calendar), [
            'title' => 'אירוע עם זמנים',
            'event_date' => '2026-03-15',
            'event_type' => 'custom',
            'start_time' => '09:00',
            'end_time' => '10:00',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('calendar_events', [
            'title' => 'אירוע עם זמנים',
            'start_time' => '09:00',
            'end_time' => '10:00',
        ]);
    }

    public function test_event_store_rejects_end_time_before_start_time(): void
    {
        $user = User::factory()->create();
        $calendar = $user->calendars()->create(['name' => 'לוח משפחתי']);

        $response = $this->actingAs($user)->post(route('calendar-events.store', $calendar), [
            'title' => 'אירוע שגוי',
            'event_date' => '2026-03-15',
            'event_type' => 'custom',
            'start_time' => '10:00',
            'end_time' => '09:00',
        ]);

        $response->assertSessionHasErrors('end_time');
    }
}
