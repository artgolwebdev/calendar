<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\MonthPageStyleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CalendarThemeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a user with a calendar that has all 12 month pages.
     */
    private function createCalendarWithMonths(): array
    {
        $user = User::factory()->create();
        $calendar = $user->calendars()->create(['name' => 'לוח משפחתי']);

        for ($month = 1; $month <= 12; $month++) {
            $calendar->monthPages()->create([
                'month_number' => $month,
                ...app(MonthPageStyleService::class)->defaults(),
            ]);
        }

        return [$user, $calendar];
    }

    public function test_theme_applies_to_all_month_pages_in_one_bulk_update(): void
    {
        [$user, $calendar] = $this->createCalendarWithMonths();

        $calendar->monthPages()->whereIn('month_number', [3, 7])->update([
            'day_box_bg_color' => '#FF0000',
            'font_choice' => 'elegant',
        ]);

        $updates = [];
        DB::listen(function ($query) use (&$updates) {
            if (str_contains($query->sql, 'update "month_pages"')) {
                $updates[] = $query->sql;
            }
        });

        $response = $this->actingAs($user)->postJson(route('calendars.themes.apply', $calendar), [
            'theme' => 'greenish',
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('theme', 'greenish');
        $response->assertJsonPath('fields.day_box_bg_color', '#DCFCE7');

        $this->assertCount(1, $updates, 'Applying a theme must be a single bulk update query');

        $this->assertSame(12, $calendar->monthPages()->where('day_box_bg_color', '#DCFCE7')->count());
        $this->assertSame(12, $calendar->monthPages()->where('font_choice', 'modern')->count());
        $this->assertSame(12, $calendar->monthPages()->where('weekday_color', '#166534')->count());
        $this->assertSame(12, $calendar->monthPages()->where('overlay_opacity', 20)->count());
        $this->assertDatabaseMissing('month_pages', ['day_box_bg_color' => '#FF0000']);
    }

    public function test_theme_apply_rejects_unknown_theme(): void
    {
        [$user, $calendar] = $this->createCalendarWithMonths();

        $response = $this->actingAs($user)->postJson(route('calendars.themes.apply', $calendar), [
            'theme' => 'neon',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('theme');

        $this->assertSame(12, $calendar->monthPages()->where('font_choice', 'default')->count());
    }

    public function test_theme_apply_requires_theme_field(): void
    {
        [$user, $calendar] = $this->createCalendarWithMonths();

        $this->actingAs($user)->postJson(route('calendars.themes.apply', $calendar), [])
            ->assertRedirect()
            ->assertSessionHasErrors('theme');
    }

    public function test_theme_applies_to_single_month_when_month_provided(): void
    {
        [$user, $calendar] = $this->createCalendarWithMonths();

        $response = $this->actingAs($user)->postJson(route('calendars.themes.apply', $calendar), [
            'theme' => 'greenish',
            'month' => 3,
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('month', 3);
        $response->assertJsonPath('fields.day_box_bg_color', '#DCFCE7');

        $this->assertSame(1, $calendar->monthPages()->where('day_box_bg_color', '#DCFCE7')->count());
        $this->assertSame(1, $calendar->monthPages()->where('font_choice', 'modern')->count());

        $monthThree = $calendar->monthPages()->where('month_number', 3)->first();
        $this->assertSame('#DCFCE7', $monthThree->day_box_bg_color);
        $this->assertSame('modern', $monthThree->font_choice);

        $monthFour = $calendar->monthPages()->where('month_number', 4)->first();
        $this->assertSame('default', $monthFour->font_choice);
        $this->assertNotSame('#DCFCE7', $monthFour->day_box_bg_color);
    }

    public function test_theme_apply_rejects_invalid_month(): void
    {
        [$user, $calendar] = $this->createCalendarWithMonths();

        $this->actingAs($user)->postJson(route('calendars.themes.apply', $calendar), [
            'theme' => 'greenish',
            'month' => 13,
        ])->assertRedirect()
            ->assertSessionHasErrors('month');

        $this->assertSame(12, $calendar->monthPages()->where('font_choice', 'default')->count());
    }

    public function test_user_cannot_apply_theme_to_another_users_calendar(): void
    {
        [, $calendar] = $this->createCalendarWithMonths();
        $otherUser = User::factory()->create();

        $this->actingAs($otherUser)->postJson(route('calendars.themes.apply', $calendar), [
            'theme' => 'blackish',
        ])->assertForbidden();

        $this->assertSame(12, $calendar->monthPages()->where('font_choice', 'default')->count());
    }

    public function test_theme_picker_renders_all_themes_with_their_values_and_both_toggle_buttons(): void
    {
        [$user, $calendar] = $this->createCalendarWithMonths();

        $response = $this->actingAs($user)->get(
            route('calendars.month', [$calendar, 1, now()->year])
        );

        $response->assertOk();

        $html = $response->getContent();

        foreach (config('themes') as $key => $theme) {
            $this->assertStringContainsString('aria-label="החל נושא '.$theme['name'].'"', $html);
            $this->assertStringContainsString($theme['day_box_bg_color'], $html);
            $this->assertStringContainsString($theme['day_box_font_color'], $html);
            $this->assertStringContainsString($theme['weekday_color'], $html);
        }

        $this->assertStringContainsString('aria-label="נושאים"', $html);
        $this->assertStringContainsString('aria-label="הגדרות עיצוב"', $html);
        $this->assertStringContainsString('להחיל את הנושא "', $html);
        $this->assertStringContainsString('על החודש הזה?', $html);
        $this->assertStringContainsString('החל נושא', $html);
        $this->assertStringContainsString('x-data="monthPage(', $html);
        $this->assertStringContainsString('month: 1,', $html);
        $this->assertStringContainsString(route('calendars.themes.apply', $calendar), $html);
    }

    public function test_year_view_renders_theme_picker_for_all_months(): void
    {
        [$user, $calendar] = $this->createCalendarWithMonths();

        $response = $this->actingAs($user)->get(route('calendars.show', $calendar));

        $response->assertOk();

        $html = $response->getContent();

        foreach (config('themes') as $theme) {
            $this->assertStringContainsString('aria-label="החל נושא '.$theme['name'].'"', $html);
        }

        $this->assertStringContainsString('aria-label="נושאים"', $html);
        $this->assertStringContainsString('על כל 12 חודשי הלוח', $html);
        $this->assertStringContainsString('להחיל את הנושא "', $html);
        $this->assertStringContainsString('על כל החודשים?', $html);
        $this->assertStringContainsString('x-data="themePicker(', $html);
        $this->assertStringContainsString(route('calendars.themes.apply', $calendar), $html);
    }

    public function test_per_month_manual_settings_still_work_after_theme_apply(): void
    {
        [$user, $calendar] = $this->createCalendarWithMonths();

        $this->actingAs($user)->postJson(route('calendars.themes.apply', $calendar), [
            'theme' => 'bluish',
        ])->assertOk();

        $monthThree = $calendar->monthPages()->where('month_number', 3)->first();

        $this->actingAs($user)->put(
            route('month-pages.update', [$calendar, $monthThree]),
            ['font_choice' => 'traditional']
        )->assertRedirect();

        $monthThree->refresh();
        $this->assertSame('traditional', $monthThree->font_choice);
        $this->assertSame('#DBEAFE', $monthThree->day_box_bg_color);

        $monthOne = $calendar->monthPages()->where('month_number', 1)->first();
        $this->assertSame('default', $monthOne->font_choice);
        $this->assertSame('#DBEAFE', $monthOne->day_box_bg_color);
    }

    public function test_theme_apply_does_not_touch_background_image_fields(): void
    {
        [$user, $calendar] = $this->createCalendarWithMonths();

        $calendar->monthPages()->where('month_number', 5)->update([
            'custom_image_path' => 'month-pages/january.jpg',
            'background_media_id' => null,
        ]);

        $this->actingAs($user)->postJson(route('calendars.themes.apply', $calendar), [
            'theme' => 'pinkish',
        ])->assertOk();

        $monthFive = $calendar->monthPages()->where('month_number', 5)->first();
        $this->assertSame('month-pages/january.jpg', $monthFive->custom_image_path);
        $this->assertSame('#FCE7F3', $monthFive->day_box_bg_color);
    }
}
