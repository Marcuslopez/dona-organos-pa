<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminAccountLockedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $lockedUser) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Alerta: cuenta administrativa bloqueada temporalmente');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.security.admin-account-locked');
    }
}
