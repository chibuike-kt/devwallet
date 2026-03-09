<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SettlementBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'wallet_id',
        'reference',
        'status',
        'total_amount',
        'currency',
        'transaction_count',
        'notes',
        'settled_at',
    ];

    protected $casts = [
        'total_amount' => 'integer',
        'settled_at'   => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function formattedTotal(): string
    {
        return $this->wallet->formatAmount($this->total_amount);
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'completed'  => 'badge-green',
            'processing' => 'badge-yellow',
            'failed'     => 'badge-red',
            'pending'    => 'badge-slate',
            default      => 'badge-slate',
        };
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    // ─── Boot ────────────────────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (SettlementBatch $batch) {
            if (empty($batch->reference)) {
                $batch->reference = 'STL-' . strtoupper(Str::random(12));
            }
        });
    }
}
