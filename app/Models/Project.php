<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'environment',
        'provider',
        'color',
        'sim_failure_rate',
        'sim_force_next_fail',
        'sim_transfer_delay',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'sim_force_next_fail' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shouldSimulateFail(): bool
    {
        // Force fail takes priority — consume it immediately
        if ($this->sim_force_next_fail) {
            $this->update(['sim_force_next_fail' => false]);
            return true;
        }

        // Probabilistic failure rate
        if ($this->sim_failure_rate > 0) {
            return rand(1, 100) <= $this->sim_failure_rate;
        }

        return false;
    }

    public function transferDelayMs(): int
    {
        return match ($this->sim_transfer_delay) {
            'slow'    => 5000,
            'timeout' => 30000,
            default   => 0,
        };
    }


    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    public function environmentLabel(): string
    {
        return match ($this->environment) {
            'test'    => 'Test',
            'staging' => 'Staging',
            default   => ucfirst($this->environment),
        };
    }

    public function isPaystack(): bool
    {
        return $this->provider === 'paystack';
    }

    public function isFlutterwave(): bool
    {
        return $this->provider === 'flutterwave';
    }

    public function isStripe(): bool
    {
        return $this->provider === 'stripe';
    }

    public function providerLabel(): string
    {
        return match ($this->provider) {
            'paystack'    => 'Paystack',
            'flutterwave' => 'Flutterwave',
            'stripe'      => 'Stripe',
            default       => ucfirst($this->provider),
        };
    }

    public function providerBaseUrl(): string
    {
        return match ($this->provider) {
            'paystack'    => url('/api/paystack'),
            'flutterwave' => url('/api/flutterwave/v3'),
            'stripe'      => url('/api/stripe/v1'),
            default       => url('/api/' . $this->provider),
        };
    }

    public function providerColor(): string
    {
        return match ($this->provider) {
            'paystack'    => '#00C3F7',
            'flutterwave' => '#F5A623',
            'stripe'      => '#635BFF',
            default       => '#0e8de6',
        };
    }

    public function initials(): string
    {
        $words = explode(' ', $this->name);
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }
        return strtoupper(substr($this->name, 0, 2));
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Project $project) {
            if (empty($project->slug)) {
                $project->slug = Str::slug($project->name) . '-' . Str::random(6);
            }
        });
    }

    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class);
    }

    public function webhookEndpoints(): HasMany
    {
        return $this->hasMany(WebhookEndpoint::class);
    }

    public function webhookEvents(): HasMany
    {
        return $this->hasMany(WebhookEvent::class);
    }

    public function settlementBatches(): HasMany
    {
        return $this->hasMany(SettlementBatch::class);
    }

    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class);
    }
}
