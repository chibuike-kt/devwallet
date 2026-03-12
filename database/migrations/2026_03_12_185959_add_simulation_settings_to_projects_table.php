<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->integer('sim_failure_rate')->default(0)->after('provider');
            $table->boolean('sim_force_next_fail')->default(false)->after('sim_failure_rate');
            $table->string('sim_transfer_delay')->default('instant')->after('sim_force_next_fail');
            // instant | slow | timeout
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'sim_failure_rate',
                'sim_force_next_fail',
                'sim_transfer_delay',
            ]);
        });
    }
};
