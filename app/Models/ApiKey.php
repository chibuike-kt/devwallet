<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'name',
        'prefix',
        'key_hash',
        'status',
        'last_used_at',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isRevoked(): bool
    {
        return $this->status === 'revoked';
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'active'  => 'badge-green',
            'revoked' => 'badge-red',
            default   => 'badge-slate',
        };
    }

    public function maskedKey(): string
    {
        return $this->prefix . str_repeat('•', 24);
    }

    // ─── Static generator ────────────────────────────────────────────────────

    /**
     * Generate a new API key for a project.
     * Returns ['model' => ApiKey, 'plaintext' => string]
     * The plaintext is shown once and never stored.
     */
    public static function generate(Project $project, string $name): array
    {
        $random    = Str::random(32);
        $plaintext = 'sk_test_' . $random;
        $prefix    = 'sk_test_' . substr($random, 0, 6);
        $hash      = hash('sha256', $plaintext);

        $model = static::create([
            'project_id' => $project->id,
            'name'       => $name,
            'prefix'     => $prefix,
            'key_hash'   => $hash,
            'status'     => 'active',
        ]);

        return [
            'model'     => $model,
            'plaintext' => $plaintext,
        ];
    }

    /**
     * Find an ApiKey model by plaintext key value.
     * Used in AuthenticateApiKey middleware.
     */
    public static function findByPlaintext(string $plaintext): ?static
    {
        $hash = hash('sha256', $plaintext);

        return static::where('key_hash', $hash)
            ->where('status', 'active')
            ->first();
    }
}
