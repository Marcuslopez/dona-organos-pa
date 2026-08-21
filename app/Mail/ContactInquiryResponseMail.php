<?php

namespace App\Mail;

use App\Models\ContactInquiry;
use App\Models\ContactInquiryReply;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactInquiryResponseMail extends Mailable
{
    use Queueable, SerializesModels;
    public function __construct(public ContactInquiry $inquiry, public ContactInquiryReply $reply) {}
    public function envelope(): Envelope { return new Envelope(subject: 'Respuesta a tu consulta | DONA ÓRGANOS PANAMÁ'); }
    public function content(): Content { return new Content(view: 'emails.contact.response'); }
}
