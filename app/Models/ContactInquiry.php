<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContactInquiry extends Model
{
    public const STATUSES = ['nueva', 'en_proceso', 'respondida', 'cerrada'];

    protected $fillable = ['name', 'email', 'message', 'status', 'assigned_to', 'assigned_at', 'responded_by', 'responded_at', 'closed_by', 'closed_at', 'privacy_accepted_at', 'privacy_policy_version', 'requester_ip', 'user_agent'];

    protected function casts(): array
    {
        return ['assigned_at' => 'datetime', 'responded_at' => 'datetime', 'closed_at' => 'datetime', 'privacy_accepted_at' => 'datetime'];
    }

    public function assignee(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }
    public function responder(): BelongsTo { return $this->belongsTo(User::class, 'responded_by'); }
    public function closer(): BelongsTo { return $this->belongsTo(User::class, 'closed_by'); }
    public function replies(): HasMany { return $this->hasMany(ContactInquiryReply::class); }
    public function history(): HasMany { return $this->hasMany(ContactInquiryHistory::class); }

    public function statusLabel(): string
    {
        return self::labelFor($this->status);
    }

    public static function labelFor(string $status): string
    {
        return [
            'nueva' => 'Nueva',
            'en_proceso' => 'En proceso',
            'respondida' => 'Respondida',
            'cerrada' => 'Cerrada',
        ][$status] ?? $status;
    }
}
