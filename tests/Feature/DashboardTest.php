<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_family_members_link_next_to_create_calendar_button(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee(route('family-members.index'));
        $response->assertSee('חברי משפחה');
        $response->assertSee('+ צור לוח שנה חדש');
        $response->assertSee(route('calendars.create'));
        $response->assertSee('flex flex-wrap items-center justify-between gap-3', false);
        $response->assertSee('w-full sm:w-auto', false);
    }
}
