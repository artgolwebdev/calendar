<?php

namespace Tests\Feature\Auth;

use App\Listeners\SendWelcomeEmail;
use App\Mail\WelcomeEmail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_registration_dispatches_registered_event_with_welcome_listener(): void
    {
        Event::fake();

        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        Event::assertDispatched(Registered::class, 1);
        Event::assertListening(Registered::class, SendWelcomeEmail::class);
    }

    public function test_registration_sends_exactly_one_welcome_email(): void
    {
        Mail::fake();

        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        Mail::assertQueued(WelcomeEmail::class, 1);
        Mail::assertQueued(WelcomeEmail::class, function (WelcomeEmail $mail): bool {
            return $mail->hasTo('test@example.com')
                && $mail->envelope()->subject === 'ברוכים הבאים ל'.config('app.name').'!';
        });
    }

    public function test_welcome_email_renders_dashboard_cta_and_user_name(): void
    {
        Mail::fake();

        $this->post('/register', [
            'name' => 'דני',
            'email' => 'danny@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        Mail::assertQueued(WelcomeEmail::class, function (WelcomeEmail $mail): bool {
            $html = $mail->render();

            return str_contains($html, '<html lang="he" dir="rtl">')
                && str_contains($html, '<body dir="rtl"')
                && str_contains($html, 'direction:rtl;text-align:right;')
                && str_contains($html, 'ברוכים הבאים')
                && str_contains($html, route('dashboard'))
                && str_contains($html, 'דני')
                && str_contains($html, 'מה עכשיו?');
        });
    }
}
