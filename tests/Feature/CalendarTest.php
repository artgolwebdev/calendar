<?php

namespace Tests\Feature;

use App\Models\Calendar;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
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

    public function test_user_can_create_a_calendar_with_a_cover_image(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/calendars', [
            'name' => 'לוח משפחתי',
            'cover_image_path' => UploadedFile::fake()->image('cover.jpg'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('calendars', [
            'user_id' => $user->id,
            'name' => 'לוח משפחתי',
        ]);

        $calendar = Calendar::first();
        $this->assertNotNull($calendar->cover_image_path);
        Storage::disk('public')->assertExists($calendar->cover_image_path);
        $this->assertStringStartsWith('calendar-covers/', $calendar->cover_image_path);
    }

    public function test_user_can_replace_the_calendar_cover_image_on_update(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $calendar = Calendar::create([
            'user_id' => $user->id,
            'name' => 'לוח משפחתי',
        ]);

        Storage::disk('public')->put('calendar-covers/old.jpg', 'old cover');
        $calendar->update(['cover_image_path' => 'calendar-covers/old.jpg']);

        $response = $this->actingAs($user)->put("/calendars/{$calendar->id}", [
            'name' => 'לוח משפחתי',
            'is_main' => 0,
            'cover_image_path' => UploadedFile::fake()->image('new.jpg'),
        ]);

        $response->assertRedirect();
        $calendar->refresh();
        $this->assertNotEquals('calendar-covers/old.jpg', $calendar->cover_image_path);
        Storage::disk('public')->assertExists($calendar->cover_image_path);
        Storage::disk('public')->assertMissing('calendar-covers/old.jpg');
    }

    public function test_yearly_view_renders_correct_hebrew_dates(): void
    {
        Http::fake(['https://www.hebcal.com/*' => Http::response(['items' => []])]);

        Carbon::setTestNow(Carbon::parse('2026-01-15'));

        $user = User::factory()->create();
        $this->actingAs($user)->post('/calendars', ['name' => 'לוח משפחתי']);
        $calendar = Calendar::first();

        $response = $this->actingAs($user)->get(route('calendars.show', $calendar));

        $response->assertOk();
        $response->assertSee('2026 · 5786');
        $response->assertSee('שבט');
        $response->assertSee('אדר');
        $response->assertDontSee('12 טבת 5786');
        $response->assertDontSee('חודש 1 · 5786');

        Carbon::setTestNow();
    }

    public function test_yearly_view_shows_month_images_and_event_chips(): void
    {
        Http::fake([
            'https://www.hebcal.com/*' => Http::response(['items' => [
                ['title' => 'פורים', 'date' => '2026-03-03', 'category' => 'holiday'],
                ['title' => 'Hebrew Language Day', 'date' => '2026-01-08', 'category' => 'minor'],
            ]]),
        ]);

        Carbon::setTestNow(Carbon::parse('2026-01-15'));

        $user = User::factory()->create();
        $this->actingAs($user)->post('/calendars', ['name' => 'לוח משפחתי']);
        $calendar = Calendar::first();

        $calendar->monthPages()->where('month_number', 1)->update([
            'custom_image_path' => 'month-pages/january.jpg',
        ]);

        $calendar->calendarEvents()->createMany([
            ['title' => 'הולדת סבא', 'event_date' => '2026-01-12', 'event_type' => 'birthday'],
            ['title' => 'מסיבת משפחה', 'event_date' => '2026-01-20', 'event_type' => 'custom'],
            ['title' => 'יום נישואין', 'event_date' => '2026-02-03', 'event_type' => 'anniversary'],
        ]);

        $response = $this->actingAs($user)->get(route('calendars.show', $calendar));

        $response->assertOk();
        $response->assertSee(asset('storage/month-pages/january.jpg'));
        $response->assertSee('הולדת סבא');
        $response->assertSee('מסיבת משפחה');
        $response->assertSee('יום נישואין');
        $response->assertSee('פורים');
        $response->assertDontSee('Hebrew Language Day');
        $response->assertDontSee('עם תמונה');

        Carbon::setTestNow();
    }
}
