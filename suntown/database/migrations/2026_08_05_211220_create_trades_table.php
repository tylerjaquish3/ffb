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
        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('season');
            $table->foreignId('proposer_team_id')->constrained('teams');
            $table->foreignId('recipient_team_id')->constrained('teams');
            $table->string('status')->default('pending');
            $table->foreignId('parent_trade_id')->nullable()->constrained('trades');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trades');
    }
};
