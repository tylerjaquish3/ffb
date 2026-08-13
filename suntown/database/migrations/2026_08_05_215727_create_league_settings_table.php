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
        Schema::create('league_settings', function (Blueprint $table) {
            $table->id();
            // How many days an accepted trade sits open to league veto votes before it executes (0-3).
            $table->unsignedTinyInteger('trade_review_days')->default(1);
            // How many days a dropped player is waiver-locked (bid-only) before becoming a plain free agent (0-4).
            $table->unsignedTinyInteger('waiver_days')->default(2);
            // FAB dollars each team starts the season with.
            $table->unsignedInteger('starting_fab_budget')->default(200);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('league_settings');
    }
};
