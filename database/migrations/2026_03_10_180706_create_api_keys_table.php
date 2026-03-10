<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();

            $table->string('name');

            // First 12 chars of key for display: sk_test_XXXX...
            $table->string('prefix', 20);

            // SHA-256 hash of the full key — never store plaintext
            $table->string('key_hash')->unique();

            // active | revoked
            $table->string('status')->default('active');

            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};
