<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->string('injury_status', 30)->nullable()->after('status');
            $table->string('injury_description', 100)->nullable()->after('injury_status');
            $table->string('injury_practice_status', 100)->nullable()->after('injury_description');
            $table->timestamp('injury_reported_at')->nullable()->after('injury_practice_status');
        });
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn(['injury_status', 'injury_description', 'injury_practice_status', 'injury_reported_at']);
        });
    }
};
