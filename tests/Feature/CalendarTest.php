<?php

namespace Tests\Feature;

use App\Models\Calendar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_a_calendar(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/calendars', [
            'name' => 'לוח משפחתי',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('calendars', [
            'user_id' => $user->id,
            'name' => 'לוח משפחתי',
        ]);

        $calendar = Calendar::first();
        $this->assertNotNull($calendar);
        $this->assertCount(12, $calendar->monthPages);
    }
}
