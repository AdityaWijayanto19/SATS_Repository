<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class ResetPasswordMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected string $resetUrl;
    protected string $email;
    protected int $expiresIn;

    public function __construct(string $email, string $token, int $expiresIn = 3600)
    {
        $this->email = $email;
        $this->expiresIn = $expiresIn / 60;

        // 🔥 signed & expiring URL
        $this->resetUrl = URL::temporarySignedRoute(
            'password.reset',
            now()->addSeconds($expiresIn),
            [
                'token' => $token,
                'email' => $email
            ]
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: config('app.name') . ' - Reset Password',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reset-password',
            with: [
                'resetUrl' => $this->resetUrl,
                'email' => $this->email,
                'expiresIn' => $this->expiresIn,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
