<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nfl_games', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('season');
            $table->unsignedInteger('week');
            $table->dateTime('kickoff_at');
            $table->foreignId('home_nfl_team_id')->nullable()->constrained('nfl_teams')->cascadeOnDelete();
            $table->foreignId('away_nfl_team_id')->nullable()->constrained('nfl_teams')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['season', 'week', 'home_nfl_team_id', 'away_nfl_team_id'], 'nfl_games_unique');
            $table->index(['season', 'week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nfl_games');
    }
};
