<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_week_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('season');
            $table->unsignedInteger('week');
            $table->foreignId('stat_category_id')->constrained()->cascadeOnDelete();
            $table->decimal('value', 8, 2);
            $table->timestamps();

            $table->unique(['player_id', 'season', 'week', 'stat_category_id'], 'player_week_stats_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_week_stats');
    }
};
