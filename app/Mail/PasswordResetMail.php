<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class PasswordResetMail extends Mailable
{
    public function __construct(public string $token, public string $email, public string $userName) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'איפוס סיסמה - '.config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset',
            with: [
                'resetUrl' => $this->resetUrl(),
                'appName' => config('app.name'),
                'userName' => $this->userName,
                'email' => $this->email,
            ],
        );
    }

    protected function resetUrl(): string
    {
        return url(route('password.reset', ['token' => $this->token, 'email' => $this->email], false));
    }
}
