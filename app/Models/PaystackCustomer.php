<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PaystackCustomer extends Model
{
    protected $fillable = [
        'project_id',
        'customer_code',
        'email',
        'first_name',
        'last_name',
        'phone',
        'metadata',
    ];

    protected $casts = ['metadata' => 'array'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PaystackTransaction::class);
    }

    public function fullName(): string
    {
        return trim("{$this->first_name} {$this->last_name}") ?: 'Unknown';
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($customer) {
            if (empty($customer->customer_code)) {
                $customer->customer_code = 'CUS_' . strtolower(Str::random(12));
            }
        });
    }
}
