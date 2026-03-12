<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('paystack_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();

            $table->string('reference')->unique();
            $table->string('transfer_code')->unique(); // TRF_xxxxxxxxxx

            // pending | success | failed | reversed
            $table->string('status')->default('pending');

            $table->unsignedBigInteger('amount'); // kobo
            $table->string('currency', 3)->default('NGN');

            $table->string('recipient_name');
            $table->string('recipient_account_number');
            $table->string('recipient_bank_code');
            $table->string('recipient_bank_name')->nullable();

            $table->string('narration')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paystack_transfers');
    }
};
