<?php

namespace Tests\Feature;

use App\Models\MonthPage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MonthPageSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function createMonthPage(): MonthPage
    {
        $user = User::factory()->create();
        $calendar = $user->calendars()->create(['name' => 'לוח משפחתי']);

        return $calendar->monthPages()->create([
            'month_number' => 1,
            'font_choice' => 'default',
            'overlay_opacity' => 30,
            'day_box_bg_color' => '#FFFFFF',
            'day_box_font_color' => '#2B2E3A',
            'day_box_bg_opacity' => 100,
            'show_adjacent_month_days' => true,
        ]);
    }

    public function test_user_can_update_all_design_settings_at_once(): void
    {
        $monthPage = $this->createMonthPage();
        $user = $monthPage->calendar->user;

        $response = $this->actingAs($user)->put(
            "/calendars/{$monthPage->calendar->id}/month-pages/{$monthPage->id}",
            [
                'font_choice' => 'modern',
                'overlay_opacity' => 60,
                'day_box_bg_color' => '#112233',
                'day_box_font_color' => '#AABBCC',
                'day_box_bg_opacity' => 45,
                'weekday_color' => '#00AABB',
                'show_adjacent_month_days' => 'on',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('month_pages', [
            'id' => $monthPage->id,
            'font_choice' => 'modern',
            'overlay_opacity' => 60,
            'day_box_bg_color' => '#112233',
            'day_box_font_color' => '#AABBCC',
            'day_box_bg_opacity' => 45,
            'weekday_color' => '#00AABB',
            'show_adjacent_month_days' => true,
        ]);
    }

    public function test_unchecked_adjacent_days_checkbox_is_saved_as_false(): void
    {
        $monthPage = $this->createMonthPage();
        $user = $monthPage->calendar->user;

        $this->actingAs($user)->put(
            "/calendars/{$monthPage->calendar->id}/month-pages/{$monthPage->id}",
            [
                'font_choice' => 'default',
                'overlay_opacity' => 30,
                'day_box_bg_color' => '#FFFFFF',
                'day_box_font_color' => '#2B2E3A',
                'day_box_bg_opacity' => 100,
                'show_adjacent_month_days' => '0',
            ]
        );

        $this->assertDatabaseHas('month_pages', [
            'id' => $monthPage->id,
            'show_adjacent_month_days' => false,
        ]);
    }

    public function test_user_can_upload_month_page_background_image(): void
    {
        Storage::fake('public');

        $monthPage = $this->createMonthPage();
        $user = $monthPage->calendar->user;

        $response = $this->actingAs($user)->put(
            "/calendars/{$monthPage->calendar->id}/month-pages/{$monthPage->id}",
            [
                'custom_image_path' => UploadedFile::fake()->image('background.jpg', 400, 300),
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $monthPage->refresh();
        $this->assertNotNull($monthPage->custom_image_path);
        Storage::disk('public')->assertExists($monthPage->custom_image_path);
    }

    public function test_uploading_new_image_replaces_previous_one(): void
    {
        Storage::fake('public');

        $monthPage = $this->createMonthPage();
        $monthPage->update(['custom_image_path' => 'month-pages/old.jpg']);
        Storage::disk('public')->put('month-pages/old.jpg', 'old-content');

        $user = $monthPage->calendar->user;

        $this->actingAs($user)->put(
            "/calendars/{$monthPage->calendar->id}/month-pages/{$monthPage->id}",
            [
                'custom_image_path' => UploadedFile::fake()->image('new.jpg', 400, 300),
            ]
        );

        $monthPage->refresh();
        $this->assertNotEquals('month-pages/old.jpg', $monthPage->custom_image_path);
        Storage::disk('public')->assertExists($monthPage->custom_image_path);
        Storage::disk('public')->assertMissing('month-pages/old.jpg');
    }

    public function test_user_can_remove_month_page_background_image(): void
    {
        Storage::fake('public');

        $monthPage = $this->createMonthPage();
        $monthPage->update(['custom_image_path' => 'month-pages/background.jpg']);
        Storage::disk('public')->put('month-pages/background.jpg', 'image-content');

        $user = $monthPage->calendar->user;

        $this->actingAs($user)->delete(
            "/calendars/{$monthPage->calendar->id}/month-pages/{$monthPage->id}"
        );

        $monthPage->refresh();
        $this->assertNull($monthPage->custom_image_path);
        Storage::disk('public')->assertMissing('month-pages/background.jpg');
    }

    public function test_rendered_month_page_applies_design_colors(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-01-15'));

        $monthPage = $this->createMonthPage();
        $monthPage->update([
            'font_choice' => 'elegant',
            'overlay_opacity' => 50,
            'day_box_bg_color' => '#FFDD00',
            'day_box_font_color' => '#123456',
            'day_box_bg_opacity' => 80,
            'weekday_color' => '#00AABB',
        ]);

        $user = $monthPage->calendar->user;

        $response = $this->actingAs($user)->get(
            "/calendars/{$monthPage->calendar->id}/month/1/".now()->year
        );

        $response->assertOk();
        $response->assertSee('rgba(255, 221, 0, 0.8)');
        $response->assertSee('#123456');
        $response->assertSee("font-family: 'Rubik', sans-serif");
        $response->assertSee('rgba(0, 0, 0, 0.5)');
        $response->assertSee('<option value="elegant"', false);
        $response->assertSee('style="color: #00AABB;"', false);
        $response->assertSee('value="#00AABB"', false);
        $response->assertSee('12 טבת');
        $response->assertSee('13 שבט');
        $response->assertDontSee('1 ניסן');
        $response->assertDontSee('12 טבת 5786');
        $response->assertSee('→ חזור ללוח');
        $response->assertSee('→ חודש קודם');
        $response->assertSee('חודש הבא ←');

        Carbon::setTestNow();
    }

    public function test_weekday_color_control_is_above_font_selector(): void
    {
        $monthPage = $this->createMonthPage();
        $user = $monthPage->calendar->user;

        $response = $this->actingAs($user)->get(
            "/calendars/{$monthPage->calendar->id}/month/1/".now()->year
        );

        $response->assertOk();

        $html = $response->getContent();
        $weekdayPos = strpos($html, 'id="weekday_color"');
        $fontPos = strpos($html, 'id="font_choice"');

        $this->assertNotFalse($weekdayPos, 'Weekday color control not found');
        $this->assertNotFalse($fontPos, 'Font selector not found');
        $this->assertLessThan($fontPos, $weekdayPos, 'Weekday color control should appear above the font selector');
    }

    public function test_save_settings_button_lives_in_offcanvas_footer_and_is_hidden_by_default(): void
    {
        $monthPage = $this->createMonthPage();
        $user = $monthPage->calendar->user;

        $response = $this->actingAs($user)->get(
            "/calendars/{$monthPage->calendar->id}/month/1/".now()->year
        );

        $response->assertOk();

        $html = $response->getContent();

        $this->assertStringContainsString('id="saveSettingsCta"', $html);
        $this->assertStringContainsString('form="designSettingsForm"', $html);
        $this->assertStringContainsString('rounded-t-3xl', $html);
        $this->assertStringContainsString('max-h-[40vh]', $html);
        $this->assertStringContainsString('שמור הגדרות', $html);

        preg_match('/<button[^>]*id="saveSettingsCta"[^>]*>/', $html, $matches);
        $this->assertNotEmpty($matches, 'Save button tag not found');
        $this->assertStringContainsString('hidden', $matches[0], 'Save button should be hidden by default');
    }

    public function test_remove_image_form_is_not_nested_inside_update_form(): void
    {
        $monthPage = $this->createMonthPage();
        $monthPage->update(['custom_image_path' => 'month-pages/test.jpg']);

        $user = $monthPage->calendar->user;

        $response = $this->actingAs($user)->get(
            "/calendars/{$monthPage->calendar->id}/month/1/".now()->year
        );

        $response->assertOk();

        $html = $response->getContent();
        $updateFormId = strpos($html, 'id="designSettingsForm"');
        $updateFormClose = strpos($html, '</form>', $updateFormId);
        $nextFormOpen = strpos($html, '<form', $updateFormId + 1);

        $this->assertNotFalse($updateFormId, 'Update form not found');
        $this->assertNotFalse($updateFormClose, 'Update form not closed');
        $this->assertFalse(
            $nextFormOpen !== false && $nextFormOpen < $updateFormClose,
            'Another form is nested inside the update form'
        );
        $this->assertStringContainsString('id="removeImageForm"', $html);
        $this->assertStringContainsString('form="removeImageForm"', $html);
    }

    public function test_user_can_save_font_choice_alone(): void
    {
        $monthPage = $this->createMonthPage();
        $user = $monthPage->calendar->user;

        $this->actingAs($user)->put(
            "/calendars/{$monthPage->calendar->id}/month-pages/{$monthPage->id}",
            ['font_choice' => 'modern']
        );

        $this->assertDatabaseHas('month_pages', [
            'id' => $monthPage->id,
            'font_choice' => 'modern',
        ]);
    }

    public function test_user_can_save_weekday_color_alone(): void
    {
        $monthPage = $this->createMonthPage();
        $user = $monthPage->calendar->user;

        $this->actingAs($user)->put(
            "/calendars/{$monthPage->calendar->id}/month-pages/{$monthPage->id}",
            ['weekday_color' => '#FF8800']
        );

        $this->assertDatabaseHas('month_pages', [
            'id' => $monthPage->id,
            'weekday_color' => '#FF8800',
        ]);
    }

    public function test_invalid_weekday_color_is_rejected(): void
    {
        $monthPage = $this->createMonthPage();
        $user = $monthPage->calendar->user;

        $response = $this->actingAs($user)->put(
            "/calendars/{$monthPage->calendar->id}/month-pages/{$monthPage->id}",
            ['weekday_color' => 'red']
        );

        $response->assertSessionHasErrors('weekday_color');
        $this->assertDatabaseHas('month_pages', [
            'id' => $monthPage->id,
            'weekday_color' => null,
        ]);
    }

    public function test_user_cannot_update_another_users_month_page(): void
    {
        $monthPage = $this->createMonthPage();
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)->put(
            "/calendars/{$monthPage->calendar->id}/month-pages/{$monthPage->id}",
            ['font_choice' => 'modern']
        );

        $response->assertForbidden();
    }

    public function test_design_settings_are_in_a_responsive_offcanvas_closed_by_default(): void
    {
        $monthPage = $this->createMonthPage();
        $user = $monthPage->calendar->user;

        $response = $this->actingAs($user)->get(
            "/calendars/{$monthPage->calendar->id}/month/1/".now()->year
        );

        $response->assertOk();

        $html = $response->getContent();
        $this->assertStringContainsString('x-show="settingsOpen"', $html);
        $this->assertStringContainsString('rounded-t-3xl', $html);
        $this->assertStringContainsString('max-h-[40vh]', $html);
        $this->assertStringContainsString('lg:rounded-l-3xl', $html);
        $this->assertStringContainsString('lg:h-full', $html);
        $this->assertStringContainsString('lg:right-0', $html);
        $this->assertStringContainsString('lg:w-[26rem]', $html);
        $this->assertStringContainsString('lg:hidden', $html);
        $this->assertStringContainsString('הגדרות עיצוב', $html);
        $this->assertStringContainsString('id="designSettingsForm"', $html);
        $this->assertStringContainsString('settingsOpen: false', $html, 'Offcanvas should be closed by default');
    }

    public function test_month_view_cover_shows_month_and_year_on_blackish_badge(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-01-15'));

        $monthPage = $this->createMonthPage();
        $monthPage->calendar->update(['cover_image_path' => 'calendar-covers/cover.jpg']);
        $user = $monthPage->calendar->user;

        $response = $this->actingAs($user)->get(
            "/calendars/{$monthPage->calendar->id}/month/1/2026"
        );

        $response->assertOk();
        $response->assertSee('bg-black/50', false);
        $response->assertSee('ינואר 2026', false);
        $response->assertSee('· טבת 5786', false);
        $response->assertSee('text-white', false);

        Carbon::setTestNow();
    }

    public function test_month_view_has_today_button_and_blue_placeholder_when_no_cover_image(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-01-15'));

        $monthPage = $this->createMonthPage();
        $user = $monthPage->calendar->user;

        $response = $this->actingAs($user)->get(
            "/calendars/{$monthPage->calendar->id}/month/1/2026"
        );

        $response->assertOk();
        $response->assertSee('היום');
        $response->assertSee(route('calendars.month', [$monthPage->calendar, now()->month, now()->year]));
        $response->assertSee('bg-gradient-to-r from-[#4F46E5] to-[#6366F1]', false);
        $response->assertSee('ינואר 2026', false);
        $response->assertSee('· טבת 5786', false);

        Carbon::setTestNow();
    }

    public function test_calendar_grid_is_inside_a_responsive_scrollable_container(): void
    {
        $monthPage = $this->createMonthPage();
        $user = $monthPage->calendar->user;

        $response = $this->actingAs($user)->get(
            "/calendars/{$monthPage->calendar->id}/month/1/".now()->year
        );

        $response->assertOk();
        $response->assertSee('overflow-x-auto', false);
        $response->assertSee('min-w-[640px]', false);
    }
}
