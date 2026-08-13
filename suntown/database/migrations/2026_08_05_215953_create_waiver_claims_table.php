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
        Schema::create('waiver_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams');
            $table->foreignId('player_id')->constrained();
            $table->unsignedInteger('season');
            $table->unsignedInteger('amount');
            // Pre-chosen roster-limit drop, used only if this team wins while at/over the limit.
            $table->foreignId('drop_player_id')->nullable()->constrained('players');
            $table->string('status')->default('pending'); // pending, won, lost, cancelled
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waiver_claims');
    }
};
