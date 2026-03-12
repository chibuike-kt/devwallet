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
        Schema::create('paystack_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('paystack_customer_id')->nullable()->constrained()->nullOnDelete();

            // PST-xxxxxxxxxx
            $table->string('reference')->unique();

            // initialized | success | failed | abandoned
            $table->string('status')->default('initialized');

            $table->unsignedBigInteger('amount'); // kobo
            $table->string('currency', 3)->default('NGN');

            $table->string('channel')->default('card'); // card | bank | ussd | mobile_money
            $table->string('gateway_response')->nullable();

            // Authorization (simulated card details)
            $table->string('authorization_code')->nullable();
            $table->string('card_type')->nullable();
            $table->string('last4')->nullable();
            $table->string('exp_month')->nullable();
            $table->string('exp_year')->nullable();
            $table->string('bank')->nullable();

            $table->string('callback_url')->nullable();
            $table->json('metadata')->nullable();

            // Simulation controls
            $table->boolean('force_fail')->default(false);
            $table->integer('delay_ms')->default(0);

            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paystack_transactions');
    }
};
