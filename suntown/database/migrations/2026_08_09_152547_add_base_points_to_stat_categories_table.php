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
        Schema::table('stat_categories', function (Blueprint $table) {
            $table->decimal('base_points', 8, 3)->default(0)->after('points_per_unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stat_categories', function (Blueprint $table) {
            $table->dropColumn('base_points');
        });
    }
};
