<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lineups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('season');
            $table->unsignedInteger('week');
            $table->foreignId('roster_position_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('slot_index')->default(1);
            $table->foreignId('player_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['team_id', 'season', 'week', 'roster_position_id', 'slot_index'], 'lineups_slot_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lineups');
    }
};
