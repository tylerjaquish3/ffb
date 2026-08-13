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
        Schema::table('trade_items', function (Blueprint $table) {
            // True for a roster-limit drop chosen during resolve() — that
            // player is just dropped, not received by the other team.
            $table->boolean('is_forced_drop')->default(false)->after('player_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trade_items', function (Blueprint $table) {
            $table->dropColumn('is_forced_drop');
        });
    }
};
