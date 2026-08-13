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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // add, drop, trade
            $table->unsignedInteger('season');
            $table->foreignId('team_id')->constrained('teams');
            $table->foreignId('player_id')->constrained();
            // For trades: the team on the other side of the swap.
            $table->foreignId('counterparty_team_id')->nullable()->constrained('teams');
            $table->foreignId('trade_id')->nullable()->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
