<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'name',
        'reference',
        'currency',
        'balance',
        'available_balance',
        'ledger_balance',
        'status',
        'metadata',
    ];

    protected $casts = [
        'balance'           => 'integer',
        'available_balance' => 'integer',
        'ledger_balance'    => 'integer',
        'metadata'          => 'array',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    // Future: transactions(), ledgerEntries() added in Phase 3

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForProject($query, int $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    // ─── Status Helpers ───────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isFrozen(): bool
    {
        return $this->status === 'frozen';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function canTransact(): bool
    {
        return $this->status === 'active';
    }

    // ─── Balance Helpers ──────────────────────────────────────────────────────

    /**
     * Human-readable formatted balance string.
     * e.g. 150000 kobo → "₦1,500.00"
     */
    public function formattedBalance(): string
    {
        return $this->formatAmount($this->balance);
    }

    public function formattedAvailableBalance(): string
    {
        return $this->formatAmount($this->available_balance);
    }

    public function formattedLedgerBalance(): string
    {
        return $this->formatAmount($this->ledger_balance);
    }

    /**
     * Convert integer minor unit to formatted currency string.
     */
    public function formatAmount(int $amount): string
    {
        $major = $amount / 100;

        return match ($this->currency) {
            'NGN' => '₦' . number_format($major, 2),
            'USD' => '$' . number_format($major, 2),
            'KES' => 'KSh' . number_format($major, 2),
            'GHS' => 'GH₵' . number_format($major, 2),
            default => $this->currency . ' ' . number_format($major, 2),
        };
    }

    /**
     * Currency symbol only.
     */
    public function currencySymbol(): string
    {
        return match ($this->currency) {
            'NGN' => '₦',
            'USD' => '$',
            'KES' => 'KSh',
            'GHS' => 'GH₵',
            default => $this->currency,
        };
    }

    /**
     * Status badge style key for views.
     */
    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'active' => 'badge-green',
            'frozen' => 'badge-yellow',
            'closed' => 'badge-red',
            default  => 'badge-slate',
        };
    }

    // ─── Boot ─────────────────────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Wallet $wallet) {
            if (empty($wallet->reference)) {
                $wallet->reference = 'WLT-' . strtoupper(Str::random(12));
            }
        });
    }
}
