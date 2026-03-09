<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WebhookEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'transaction_id',
        'event_type',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function eventTypeBadgeClass(): string
    {
        return match (true) {
            str_contains($this->event_type, 'success') => 'badge-green',
            str_contains($this->event_type, 'failed')  => 'badge-red',
            str_contains($this->event_type, 'pending') => 'badge-yellow',
            str_contains($this->event_type, 'reversal') => 'badge-yellow',
            default => 'badge-blue',
        };
    }
}
