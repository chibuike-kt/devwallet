<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'webhook_event_id',
        'webhook_endpoint_id',
        'status',
        'http_status',
        'response_body',
        'duration_ms',
        'attempt_number',
        'attempted_at',
        'failure_reason',
    ];

    protected $casts = [
        'attempted_at' => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function event(): BelongsTo
    {
        return $this->belongsTo(WebhookEvent::class, 'webhook_event_id');
    }

    public function endpoint(): BelongsTo
    {
        return $this->belongsTo(WebhookEndpoint::class, 'webhook_endpoint_id');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'success' => 'badge-green',
            'failed'  => 'badge-red',
            'pending' => 'badge-yellow',
            default   => 'badge-slate',
        };
    }
}
