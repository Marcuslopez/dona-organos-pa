<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DonorAccessCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $name, public string $code) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Código para continuar con tu registro | DONA ÓRGANOS PANAMÁ');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.security.donor-access-code');
    }
}
