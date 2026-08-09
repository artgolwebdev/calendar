<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_navigation_links_to_family_members(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee(route('family-members.index'));
        $response->assertSee('חברי משפחה');
        $response->assertDontSee('לוח השנה שלי');
        $response->assertDontSee('נהל וצפה בלוחות השנה המשפחתיים שלך');
    }

    public function test_create_calendar_trigger_is_in_side_menu(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSeeInOrder([
            'פרופיל',
            route('calendars.create'),
            'לוח שנה חדש',
            'חברי משפחה',
            'הספרייה שלי',
        ]);
    }

    public function test_dashboard_no_longer_shows_create_calendar_button_in_top_section(): void
    {
        $user = User::factory()->create();
        $user->calendars()->create(['name' => 'לוח משפחתי']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertDontSee('+ צור לוח שנה חדש');
    }

    public function test_dashboard_renders_two_week_day_scroller_for_main_calendar(): void
    {
        Http::fake(['https://www.hebcal.com/*' => Http::response(['items' => []])]);

        Carbon::setTestNow(Carbon::parse('2026-03-15'));

        $user = User::factory()->create();
        $calendar = $user->calendars()->create(['name' => 'לוח משפחתי']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertDontSee('החודש הנוכחי');
        $response->assertSee('לוח ראשי: לוח משפחתי');
        $response->assertSee('החודש המלא');
        $response->assertSee('data-day-scroll-target="today"', false);
        $response->assertSee(route('calendars.day', [$calendar, '2026-03-15']));
        $response->assertSee('היום');
        $response->assertSee('day-hebrew', false);
        $response->assertSee('onPointerDown', false);
        $response->assertSee('is-dragging', false);
        $this->assertSame(14, substr_count($response->getContent(), 'class="day-scroller-card'));

        Carbon::setTestNow();
    }

    public function test_day_scroller_spans_into_next_month(): void
    {
        Http::fake(['https://www.hebcal.com/*' => Http::response(['items' => []])]);

        Carbon::setTestNow(Carbon::parse('2026-05-31'));

        $user = User::factory()->create();
        $calendar = $user->calendars()->create(['name' => 'לוח משפחתי']);
        $calendar->calendarEvents()->create([
            'title' => 'ימי ההולדת של יוני',
            'event_date' => '2026-06-03',
            'event_type' => 'birthday',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee(route('calendars.day', [$calendar, '2026-06-03']));
        $response->assertSee('title="ימי ההולדת של יוני"', false);

        Carbon::setTestNow();
    }

    public function test_dashboard_day_scroller_shows_holidays_and_events(): void
    {
        Http::fake([
            'https://www.hebcal.com/*' => Http::response(['items' => [
                ['title' => 'פורים', 'date' => '2026-03-23', 'category' => 'holiday'],
            ]]),
        ]);

        Carbon::setTestNow(Carbon::parse('2026-03-15'));

        $user = User::factory()->create();
        $calendar = $user->calendars()->create(['name' => 'לוח משפחתי']);
        $calendar->calendarEvents()->create([
            'title' => 'הולדת סבא',
            'event_date' => '2026-03-20',
            'event_type' => 'birthday',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('title="פורים"', false);
        $response->assertSee('title="הולדת סבא"', false);
        $response->assertSee('פורים');
        $response->assertSee('הולדת סבא');
        $response->assertSee('chip-holiday', false);
        $response->assertSee('chip-birthday', false);

        Carbon::setTestNow();
    }
}
