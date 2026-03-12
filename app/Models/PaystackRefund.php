<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PaystackRefund extends Model
{
    protected $fillable = [
        'project_id',
        'paystack_transaction_id',
        'reference',
        'status',
        'amount',
        'currency',
        'merchant_note',
        'customer_note',
        'processed_at',
    ];

    protected $casts = ['processed_at' => 'datetime'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PaystackTransaction::class, 'paystack_transaction_id');
    }

    public function formattedAmount(): string
    {
        return '₦' . number_format($this->amount / 100, 2);
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($refund) {
            if (empty($refund->reference)) {
                $refund->reference = 'REF_' . strtoupper(Str::random(10));
            }
        });
    }
}
