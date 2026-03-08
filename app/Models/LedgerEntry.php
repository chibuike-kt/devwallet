<?php

namespace App\Models;

use App\Enums\LedgerDirection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LedgerEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'transaction_id',
        'direction',
        'amount',
        'currency',
        'running_balance',
        'narration',
        'metadata',
    ];

    protected $casts = [
        'amount'          => 'integer',
        'running_balance' => 'integer',
        'metadata'        => 'array',
        'direction'       => LedgerDirection::class,
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function formattedAmount(): string
    {
        return $this->wallet->formatAmount($this->amount);
    }

    public function formattedRunningBalance(): string
    {
        return $this->wallet->formatAmount($this->running_balance);
    }

    public function isCredit(): bool
    {
        return $this->direction === LedgerDirection::Credit;
    }

    public function isDebit(): bool
    {
        return $this->direction === LedgerDirection::Debit;
    }
}
