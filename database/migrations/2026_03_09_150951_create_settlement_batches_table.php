<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settlement_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();

            $table->string('reference')->unique();

            // pending | processing | completed | failed
            $table->string('status')->default('pending');

            // Total amount settled in minor units
            $table->unsignedBigInteger('total_amount')->default(0);
            $table->string('currency', 3);

            // How many transactions included
            $table->unsignedInteger('transaction_count')->default(0);

            $table->text('notes')->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'status']);
            $table->index('wallet_id');
        });

        // Add settlement_batch_id to transactions table
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('settlement_batch_id')
                ->nullable()
                ->after('metadata')
                ->constrained('settlement_batches')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\SettlementBatch::class);
            $table->dropColumn('settlement_batch_id');
        });

        Schema::dropIfExists('settlement_batches');
    }
};
