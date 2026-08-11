<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WelcomePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake(['https://www.hebcal.com/*' => Http::response(['items' => []])]);
    }

    public function test_guest_can_view_the_welcome_page(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('לוח שנה משפחתי בעברית');
        $response->assertSee('צור חשבון');
        $response->assertSee('התחברות');
    }

    public function test_calendar_preview_wraps_in_a_mobile_scroll_container(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('calendarPreviewScroll', false);
        $response->assertSee('overflow-x-auto overscroll-x-contain', false);
        $response->assertSee('min-w-[34rem] md:min-w-0', false);
        $response->assertSee('showIndicator', false);
        $response->assertSee('dir="ltr"', false);
    }

    public function test_calendar_preview_shows_mobile_scroll_affordances(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('bg-gradient-to-l from-white to-transparent', false);
        $response->assertSee('bg-gradient-to-r from-white to-transparent', false);
    }

    public function test_calendar_preview_keeps_seven_day_columns(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('ראשון');
        $response->assertSee('שבת');
        $response->assertSee('grid-cols-7', false);
    }

    public function test_hero_ctas_render_inline_side_by_side(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('flex flex-wrap justify-center items-center gap-3 mt-6', false);
        $response->assertDontSee('w-full sm:w-auto', false);
        $response->assertDontSee('flex flex-col sm:flex-row', false);
    }
}
