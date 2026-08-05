<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response
            ->assertStatus(200)
            ->assertSee('dir="rtl"', false)
            ->assertSee('שכחת את הסיסמה?')
            ->assertSee('שלח קישור איפוס');
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) {
            $response = $this->get('/reset-password/'.$notification->token);

            $response
                ->assertStatus(200)
                ->assertSee('איפוס סיסמה')
                ->assertSee('סיסמה חדשה');

            return true;
        });
    }

    public function test_reset_password_email_is_hebrew_rtl(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function (ResetPasswordNotification $notification) use ($user) {
            $mail = $notification->toMail($user);
            $html = $mail->render();

            $this->assertStringContainsString('lang="he" dir="rtl"', $html);
            $this->assertStringContainsString('איפוס סיסמה', $html);
            $this->assertStringContainsString(config('app.name'), $html);
            $this->assertStringContainsString(str_replace('@', '%40', $user->email), $html);
            $this->assertStringContainsString(
                route('password.reset', ['token' => $notification->token, 'email' => $user->email]),
                $html
            );

            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use ($user) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('login'));

            $this->assertTrue(Hash::check('password', $user->fresh()->password));

            return true;
        });
    }

    public function test_password_can_be_reset_and_user_can_login_with_new_password(): void
    {
        Notification::fake();

        $user = User::factory()->create(['password' => 'old-password']);

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use ($user) {
            $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('login'));

            $this->post('/login', [
                'email' => $user->email,
                'password' => 'new-password',
            ])->assertSessionHasNoErrors();

            $this->assertAuthenticated();

            return true;
        });
    }

    public function test_reset_password_link_shows_error_for_unknown_email(): void
    {
        $this->from('/forgot-password')->post('/forgot-password', [
            'email' => 'nobody@example.com',
        ])->assertSessionHasErrors('email');

        $this->assertEquals('לא מצאנו משתמש עם כתובת האימייל הזו.', session('errors')->first('email'));
    }

    public function test_reset_password_link_shows_error_for_invalid_email_format(): void
    {
        $this->from('/forgot-password')->post('/forgot-password', [
            'email' => 'not-an-email',
        ])->assertSessionHasErrors('email');

        $this->assertEquals('כתובת האימייל אינה תקינה.', session('errors')->first('email'));
    }

    public function test_password_cannot_be_reset_with_invalid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $response = $this->post('/reset-password', [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertSessionHasErrors('email');

        $this->assertEquals('טוקן איפוס הסיסמה אינו תקין.', session('errors')->first('email'));
    }

    public function test_password_cannot_be_reset_with_mismatched_confirmation(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use ($user) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'new-password',
                'password_confirmation' => 'different-password',
            ]);

            $response->assertSessionHasErrors('password');

            $this->assertEquals('שדה האישור אינו תואם לשדה הסיסמה.', session('errors')->first('password'));

            return true;
        });
    }
}
