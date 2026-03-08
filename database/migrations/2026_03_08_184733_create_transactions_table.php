<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();

            $table->string('reference')->unique();

            // Idempotency: if same key arrives twice, return original
            $table->string('idempotency_key')->nullable()->unique();

            $table->string('type');   // TransactionType enum value
            $table->string('status'); // TransactionStatus enum value

            // Amount always stored in minor units (kobo, cents)
            $table->unsignedBigInteger('amount');
            $table->string('currency', 3);

            // For transfers: the other wallet involved
            $table->foreignId('related_wallet_id')
                ->nullable()
                ->constrained('wallets')
                ->nullOnDelete();

            // Balance snapshot AFTER this transaction applied
            $table->unsignedBigInteger('balance_before');
            $table->unsignedBigInteger('balance_after');

            $table->string('narration')->nullable();
            $table->string('provider')->nullable();       // e.g. paystack, flutterwave
            $table->string('provider_reference')->nullable();
            $table->text('failure_reason')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['wallet_id', 'status']);
            $table->index(['project_id', 'type']);
            $table->index('reference');
            $table->index('idempotency_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
