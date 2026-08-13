<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('draft_picks', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('season');
            $table->unsignedInteger('round');
            $table->unsignedInteger('pick_number');
            $table->unsignedInteger('overall_pick');
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('roster_position_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['season', 'overall_pick']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('draft_picks');
    }
};
