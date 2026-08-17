<?php

namespace App\Mail;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class DonorCardMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $card) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Tu carné de donante | DONA ÓRGANOS PANAMÁ');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.donor-card');
    }

    public function attachments(): array
    {
        $filename = 'carnet-'.$this->card['record']->folio.'-'.Str::slug($this->card['record']->full_name).'.pdf';

        return [Attachment::fromData(
            fn (): string => Pdf::loadView('cards.pdf', ['card' => $this->card])->setPaper('letter', 'landscape')->output(),
            $filename,
        )->withMime('application/pdf')];
    }
}
