<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MainCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_can_set_calendar_as_main(): void
    {
        $user = User::factory()->create();
        $first = $user->calendars()->create(['name' => 'לוח א']);
        $second = $user->calendars()->create(['name' => 'לוח ב']);

        $response = $this->actingAs($user)->put(route('calendars.update', $second), [
            'name' => 'לוח ב',
            'is_main' => '1',
        ]);

        $response->assertRedirect();
        $this->assertTrue($second->refresh()->is_main);
        $this->assertFalse($first->refresh()->is_main);
    }

    public function test_setting_a_new_main_calendar_unsets_the_previous_one(): void
    {
        $user = User::factory()->create();
        $first = $user->calendars()->create(['name' => 'לוח א']);
        $second = $user->calendars()->create(['name' => 'לוח ב']);

        $this->actingAs($user)->put(route('calendars.update', $first), ['name' => 'לוח א', 'is_main' => '1']);
        $this->actingAs($user)->put(route('calendars.update', $second), ['name' => 'לוח ב', 'is_main' => '1']);

        $this->assertFalse($first->refresh()->is_main);
        $this->assertTrue($second->refresh()->is_main);
    }

    public function test_unchecking_is_main_clears_the_main_flag(): void
    {
        $user = User::factory()->create();
        $calendar = $user->calendars()->create(['name' => 'לוח א', 'is_main' => true]);

        $this->actingAs($user)->put(route('calendars.update', $calendar), [
            'name' => 'לוח א',
            'is_main' => '0',
        ]);

        $this->assertFalse($calendar->refresh()->is_main);
    }

    public function test_main_calendar_resolver_falls_back_to_oldest_calendar(): void
    {
        $user = User::factory()->create();
        $first = $user->calendars()->create(['name' => 'לוח א']);
        $second = $user->calendars()->create(['name' => 'לוח ב']);

        $this->assertSame($first->id, $user->mainCalendar()->id);
    }

    public function test_main_calendar_resolver_prefers_explicitly_marked_calendar(): void
    {
        $user = User::factory()->create();
        $user->calendars()->create(['name' => 'לוח א']);
        $second = $user->calendars()->create(['name' => 'לוח ב', 'is_main' => true]);

        $this->assertSame($second->id, $user->mainCalendar()->id);
    }

    public function test_main_calendar_resolver_returns_null_without_calendars(): void
    {
        $user = User::factory()->create();

        $this->assertNull($user->mainCalendar());
    }
}
