<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class WebhookEndpoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'url',
        'description',
        'events',
        'secret',
        'status',
    ];

    protected $casts = [
        'events' => 'array',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function maskedSecret(): string
    {
        return 'whsec_' . str_repeat('*', 20) . Str::substr($this->secret, -4);
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'active'   => 'badge-green',
            'disabled' => 'badge-slate',
            default    => 'badge-slate',
        };
    }

    // ─── Boot ────────────────────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (WebhookEndpoint $endpoint) {
            if (empty($endpoint->secret)) {
                $endpoint->secret = Str::random(32);
            }
        });
    }
}
