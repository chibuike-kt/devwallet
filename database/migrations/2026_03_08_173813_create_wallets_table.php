<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->string('reference')->unique();
            $table->string('currency', 3)->default('NGN');

            // Balances stored in smallest currency unit (kobo, cents)
            // Never use decimal/float for money
            $table->unsignedBigInteger('balance')->default(0);
            $table->unsignedBigInteger('available_balance')->default(0);
            $table->unsignedBigInteger('ledger_balance')->default(0);

            // active | frozen | closed
            $table->string('status')->default('active');

            // Flexible metadata: account holder name, bank code, etc.
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index(['project_id', 'status']);
            $table->index('reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
