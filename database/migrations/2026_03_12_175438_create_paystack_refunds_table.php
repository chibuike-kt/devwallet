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
        Schema::create('paystack_refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('paystack_transaction_id')->constrained()->cascadeOnDelete();

            $table->string('reference')->unique();

            // pending | processed | failed
            $table->string('status')->default('pending');

            $table->unsignedBigInteger('amount'); // kobo — partial refunds supported
            $table->string('currency', 3)->default('NGN');

            $table->string('merchant_note')->nullable();
            $table->string('customer_note')->nullable();

            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paystack_refunds');
    }
};
