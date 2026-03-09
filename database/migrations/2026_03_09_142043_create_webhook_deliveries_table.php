<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('webhook_event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('webhook_endpoint_id')->constrained()->cascadeOnDelete();

            // pending | success | failed
            $table->string('status')->default('pending');

            // HTTP response details
            $table->integer('http_status')->nullable();
            $table->text('response_body')->nullable();
            $table->integer('duration_ms')->nullable();

            // Attempt tracking
            $table->unsignedInteger('attempt_number')->default(1);
            $table->timestamp('attempted_at')->nullable();

            // Failure details
            $table->text('failure_reason')->nullable();

            $table->timestamps();

            $table->index(['webhook_event_id', 'status']);
            $table->index('webhook_endpoint_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_deliveries');
    }
};
