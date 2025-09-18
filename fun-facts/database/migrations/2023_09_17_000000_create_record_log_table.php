<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecordLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('record_log', function (Blueprint $table) {
            $table->increments('id');
            $table->string('manager_id');
            $table->integer('year');
            $table->integer('week');
            $table->integer('fun_fact_id');
            $table->string('value');
            $table->text('note')->nullable();
            $table->boolean('new_leader')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('record_log');
    }
}
