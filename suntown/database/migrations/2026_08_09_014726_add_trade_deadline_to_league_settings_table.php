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
        Schema::table('league_settings', function (Blueprint $table) {
            // Last day new trades can be proposed; null means no deadline.
            $table->date('trade_deadline')->nullable()->default('2026-12-01');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('league_settings', function (Blueprint $table) {
            $table->dropColumn('trade_deadline');
        });
    }
};
