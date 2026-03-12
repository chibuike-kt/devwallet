<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PaystackTransfer extends Model
{
    protected $fillable = [
        'project_id',
        'reference',
        'transfer_code',
        'status',
        'amount',
        'currency',
        'recipient_name',
        'recipient_account_number',
        'recipient_bank_code',
        'recipient_bank_name',
        'narration',
        'metadata',
        'completed_at',
    ];

    protected $casts = [
        'metadata'     => 'array',
        'completed_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function formattedAmount(): string
    {
        return '₦' . number_format($this->amount / 100, 2);
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'success'  => 'badge-green',
            'failed'   => 'badge-red',
            'reversed' => 'badge-yellow',
            'pending'  => 'badge-slate',
            default    => 'badge-slate',
        };
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($transfer) {
            if (empty($transfer->transfer_code)) {
                $transfer->transfer_code = 'TRF_' . strtolower(Str::random(12));
            }
            if (empty($transfer->reference)) {
                $transfer->reference = 'DEV_TRF_' . strtoupper(Str::random(10));
            }
        });
    }
}
