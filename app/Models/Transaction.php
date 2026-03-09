<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'project_id',
        'reference',
        'idempotency_key',
        'type',
        'status',
        'amount',
        'currency',
        'related_wallet_id',
        'balance_before',
        'balance_after',
        'narration',
        'provider',
        'provider_reference',
        'failure_reason',
        'metadata',
        'settlement_batch_id',
        'completed_at',
    ];

    protected $casts = [
        'amount'         => 'integer',
        'balance_before' => 'integer',
        'balance_after'  => 'integer',
        'metadata'       => 'array',
        'completed_at'   => 'datetime',
        'type'           => TransactionType::class,
        'status'         => TransactionStatus::class,
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function relatedWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'related_wallet_id');
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function formattedAmount(): string
    {
        return $this->wallet->formatAmount($this->amount);
    }

    public function isPending(): bool
    {
        return $this->status === TransactionStatus::Pending;
    }

    public function isSuccess(): bool
    {
        return $this->status === TransactionStatus::Success;
    }

    public function isFailed(): bool
    {
        return $this->status === TransactionStatus::Failed;
    }

    public function isReversed(): bool
    {
        return $this->status === TransactionStatus::Reversed;
    }

    public function settlementBatch(): BelongsTo
    {
        return $this->belongsTo(SettlementBatch::class);
    }

    // ─── Boot ─────────────────────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Transaction $tx) {
            if (empty($tx->reference)) {
                $tx->reference = 'TXN-' . strtoupper(Str::random(14));
            }
        });
    }
}
