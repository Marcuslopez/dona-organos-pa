<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactInquiryHistory extends Model
{
    protected $fillable = ['contact_inquiry_id', 'actor_id', 'action', 'previous_status', 'current_status', 'metadata'];
    protected function casts(): array { return ['metadata' => 'array']; }
    public function inquiry(): BelongsTo { return $this->belongsTo(ContactInquiry::class, 'contact_inquiry_id'); }
    public function actor(): BelongsTo { return $this->belongsTo(User::class, 'actor_id'); }
}
