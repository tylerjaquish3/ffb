<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('nfl_team_id')->nullable()->constrained('nfl_teams')->nullOnDelete();
            $table->string('position', 10);
            $table->unsignedBigInteger('external_id')->nullable()->unique();
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->index('position');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
