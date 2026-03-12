<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PaystackTransaction extends Model
{
    protected $fillable = [
        'project_id',
        'paystack_customer_id',
        'reference',
        'status',
        'amount',
        'currency',
        'channel',
        'gateway_response',
        'authorization_code',
        'card_type',
        'last4',
        'exp_month',
        'exp_year',
        'bank',
        'callback_url',
        'metadata',
        'force_fail',
        'delay_ms',
        'paid_at',
    ];

    protected $casts = [
        'metadata'   => 'array',
        'force_fail' => 'boolean',
        'paid_at'    => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(PaystackCustomer::class, 'paystack_customer_id');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(PaystackRefund::class);
    }

    public function formattedAmount(): string
    {
        return '₦' . number_format($this->amount / 100, 2);
    }

    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
    public function isPending(): bool
    {
        return $this->status === 'initialized';
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'success'     => 'badge-green',
            'failed'      => 'badge-red',
            'abandoned'   => 'badge-slate',
            'initialized' => 'badge-yellow',
            default       => 'badge-slate',
        };
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($tx) {
            if (empty($tx->reference)) {
                $tx->reference = strtolower(Str::random(16));
            }
        });
    }
}
