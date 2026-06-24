<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class WelcomeCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $recipientName,
        public readonly string $recipientEmail,
        public readonly ?string $plainPassword,
        public readonly string $loginUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your ' . config('app.name') . ' Account Credentials',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.welcome-credentials',
        );
    }
}
