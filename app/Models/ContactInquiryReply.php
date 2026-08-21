<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactInquiryReply extends Model
{
    protected $fillable = ['contact_inquiry_id', 'author_id', 'body', 'sent_at', 'delivery_status'];
    protected function casts(): array { return ['sent_at' => 'datetime']; }
    public function inquiry(): BelongsTo { return $this->belongsTo(ContactInquiry::class, 'contact_inquiry_id'); }
    public function author(): BelongsTo { return $this->belongsTo(User::class, 'author_id'); }
}
