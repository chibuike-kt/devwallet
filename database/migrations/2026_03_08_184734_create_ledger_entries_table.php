<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();

            $table->string('direction'); // LedgerDirection enum value: credit | debit

            // Amount in minor units
            $table->unsignedBigInteger('amount');
            $table->string('currency', 3);

            // Running balance after this entry
            $table->unsignedBigInteger('running_balance');

            $table->string('narration')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index(['wallet_id', 'direction']);
            $table->index('transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
    }
};
