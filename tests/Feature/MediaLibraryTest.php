<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaLibraryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<int, string>  $names
     * @return array{0: User, 1: Collection<int, Media>}
     */
    private function createUserWithMedia(array $names = ['family.jpg']): array
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $media = collect();
        foreach ($names as $name) {
            $media->push($user->addMedia(UploadedFile::fake()->image($name))->toMediaCollection('user_media'));
        }

        return [$user, $media];
    }

    public function test_guest_cannot_access_media_library(): void
    {
        $this->get('/media')->assertRedirect('/login');
        $this->post('/media')->assertRedirect('/login');
        $this->get('/media/upload')->assertRedirect('/login');
    }

    public function test_user_can_view_empty_media_library(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/media');

        $response->assertOk();
        $response->assertSee('הספרייה שלי');
        $response->assertSee('אין עדיין תמונות בספרייה');
    }

    public function test_user_can_upload_images_to_media_library(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/media', [
            'files' => [
                UploadedFile::fake()->image('family.jpg', 400, 300),
                UploadedFile::fake()->image('dog.png', 200, 200),
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseCount('media', 2);
        $this->assertDatabaseHas('media', [
            'model_type' => User::class,
            'model_id' => $user->id,
            'collection_name' => 'user_media',
        ]);

        foreach ($user->getMedia('user_media') as $media) {
            Storage::disk('public')->assertExists($media->getPathRelativeToRoot());
        }
    }

    public function test_upload_rejects_non_image_files(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/media', [
            'files' => [UploadedFile::fake()->create('notes.txt', 10, 'text/plain')],
        ]);

        $response->assertSessionHasErrors('files.0');
        $this->assertDatabaseCount('media', 0);
    }

    public function test_upload_returns_json_for_xhr_requests(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/media', ['files' => [UploadedFile::fake()->image('photo.jpg')]], ['Accept' => 'application/json']);

        $response->assertOk();
        $response->assertJson(['success' => 'התמונות הועלו בהצלחה']);
    }

    public function test_user_can_rename_media_item(): void
    {
        [$user, $media] = $this->createUserWithMedia();
        $item = $media->first();

        $this->actingAs($user)->put("/media/{$item->id}", ['name' => 'תמונת משפחה']);

        $this->assertDatabaseHas('media', ['id' => $item->id, 'name' => 'תמונת משפחה']);
    }

    public function test_user_can_delete_media_item(): void
    {
        [$user, $media] = $this->createUserWithMedia();
        $item = $media->first();

        $this->actingAs($user)->delete("/media/{$item->id}");

        $this->assertDatabaseMissing('media', ['id' => $item->id]);
    }

    public function test_deleting_media_clears_month_page_background_references(): void
    {
        [$user, $media] = $this->createUserWithMedia();
        $item = $media->first();

        $calendar = $user->calendars()->create(['name' => 'לוח משפחתי']);
        $monthPage = $calendar->monthPages()->create([
            'month_number' => 1,
            'background_media_id' => $item->id,
            'font_choice' => 'default',
            'overlay_opacity' => 30,
            'day_box_bg_color' => '#FFFFFF',
            'day_box_font_color' => '#2B2E3A',
            'day_box_bg_opacity' => 100,
            'show_adjacent_month_days' => true,
        ]);

        $this->actingAs($user)->delete("/media/{$item->id}");

        $this->assertDatabaseHas('month_pages', ['id' => $monthPage->id, 'background_media_id' => null]);
        $this->assertDatabaseMissing('media', ['id' => $item->id]);
    }

    public function test_user_cannot_rename_another_users_media(): void
    {
        [$owner, $media] = $this->createUserWithMedia();
        $otherUser = User::factory()->create();

        $this->actingAs($otherUser)->put("/media/{$media->first()->id}", ['name' => 'שלי'])->assertForbidden();
    }

    public function test_user_cannot_delete_another_users_media(): void
    {
        [$owner, $media] = $this->createUserWithMedia();
        $otherUser = User::factory()->create();

        $this->actingAs($otherUser)->delete("/media/{$media->first()->id}")->assertForbidden();

        $this->assertDatabaseCount('media', 1);
    }

    public function test_user_can_set_month_background_from_media_library(): void
    {
        [$user, $media] = $this->createUserWithMedia();
        $item = $media->first();

        $calendar = $user->calendars()->create(['name' => 'לוח משפחתי']);
        $monthPage = $calendar->monthPages()->create([
            'month_number' => 1,
            'font_choice' => 'default',
            'overlay_opacity' => 30,
            'day_box_bg_color' => '#FFFFFF',
            'day_box_font_color' => '#2B2E3A',
            'day_box_bg_opacity' => 100,
            'show_adjacent_month_days' => true,
        ]);

        $response = $this->actingAs($user)->put(
            "/calendars/{$calendar->id}/month-pages/{$monthPage->id}",
            ['background_media_id' => $item->id]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('month_pages', ['id' => $monthPage->id, 'background_media_id' => $item->id]);
    }

    public function test_clearing_media_selection_sets_background_to_null(): void
    {
        [$user, $media] = $this->createUserWithMedia();
        $item = $media->first();

        $calendar = $user->calendars()->create(['name' => 'לוח משפחתי']);
        $monthPage = $calendar->monthPages()->create([
            'month_number' => 1,
            'background_media_id' => $item->id,
            'font_choice' => 'default',
            'overlay_opacity' => 30,
            'day_box_bg_color' => '#FFFFFF',
            'day_box_font_color' => '#2B2E3A',
            'day_box_bg_opacity' => 100,
            'show_adjacent_month_days' => true,
        ]);

        $this->actingAs($user)->put(
            "/calendars/{$calendar->id}/month-pages/{$monthPage->id}",
            ['background_media_id' => '']
        );

        $this->assertDatabaseHas('month_pages', ['id' => $monthPage->id, 'background_media_id' => null]);
    }

    public function test_uploading_direct_image_clears_media_background(): void
    {
        [$user, $media] = $this->createUserWithMedia();
        $item = $media->first();

        $calendar = $user->calendars()->create(['name' => 'לוח משפחתי']);
        $monthPage = $calendar->monthPages()->create([
            'month_number' => 1,
            'background_media_id' => $item->id,
            'font_choice' => 'default',
            'overlay_opacity' => 30,
            'day_box_bg_color' => '#FFFFFF',
            'day_box_font_color' => '#2B2E3A',
            'day_box_bg_opacity' => 100,
            'show_adjacent_month_days' => true,
        ]);

        $this->actingAs($user)->put(
            "/calendars/{$calendar->id}/month-pages/{$monthPage->id}",
            ['custom_image_path' => UploadedFile::fake()->image('new.jpg', 400, 300)]
        );

        $this->assertDatabaseHas('month_pages', ['id' => $monthPage->id, 'background_media_id' => null]);
    }

    public function test_user_cannot_use_another_users_media_as_background(): void
    {
        [$owner, $media] = $this->createUserWithMedia();
        $otherUser = User::factory()->create();
        $calendar = $otherUser->calendars()->create(['name' => 'לוח אחר']);
        $monthPage = $calendar->monthPages()->create([
            'month_number' => 1,
            'font_choice' => 'default',
            'overlay_opacity' => 30,
            'day_box_bg_color' => '#FFFFFF',
            'day_box_font_color' => '#2B2E3A',
            'day_box_bg_opacity' => 100,
            'show_adjacent_month_days' => true,
        ]);

        $this->actingAs($otherUser)->put(
            "/calendars/{$calendar->id}/month-pages/{$monthPage->id}",
            ['background_media_id' => $media->first()->id]
        )->assertForbidden();

        $this->assertDatabaseHas('month_pages', ['id' => $monthPage->id, 'background_media_id' => null]);
    }

    public function test_month_view_renders_media_background_and_picker(): void
    {
        [$user, $media] = $this->createUserWithMedia();
        $item = $media->first();

        $calendar = $user->calendars()->create(['name' => 'לוח משפחתי']);
        $monthPage = $calendar->monthPages()->create([
            'month_number' => 1,
            'background_media_id' => $item->id,
            'font_choice' => 'default',
            'overlay_opacity' => 30,
            'day_box_bg_color' => '#FFFFFF',
            'day_box_font_color' => '#2B2E3A',
            'day_box_bg_opacity' => 100,
            'show_adjacent_month_days' => true,
        ]);

        $response = $this->actingAs($user)->get("/calendars/{$calendar->id}/month/1/".now()->year);

        $response->assertOk();
        $response->assertSee($item->getUrl());
        $response->assertSee('רקע מהספרייה');
        $response->assertSee('בחירת תמונה מהספרייה');
        $response->assertSee($item->getUrl('thumb'));
    }

    public function test_media_index_lists_uploaded_images(): void
    {
        [$user, $media] = $this->createUserWithMedia(['family.jpg', 'dog.png']);

        $response = $this->actingAs($user)->get('/media');

        $response->assertOk();
        $response->assertSee('התמונות שלי (2)');
        $response->assertSee($media[0]->getUrl('thumb'));
        $response->assertSee($media[1]->getUrl('thumb'));
    }

    public function test_upload_view_renders_dropzone_and_folder_selector(): void
    {
        $user = User::factory()->create();
        $user->folders()->create(['name' => 'חופשה']);

        $response = $this->actingAs($user)->get('/media/upload');

        $response->assertOk();
        $response->assertSee('העלאת תמונות');
        $response->assertSee('בחירת קבצים');
        $response->assertSee('חופשה');
        $response->assertSee(route('media.index'));
    }

    public function test_library_view_links_to_upload_view(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/media');

        $response->assertOk();
        $response->assertSee(route('media.create'));
    }

    public function test_upload_view_links_back_to_library(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/media/upload');

        $response->assertOk();
        $response->assertSee(route('media.index'));
    }
}
