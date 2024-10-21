<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResultOfCompetitionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('result_of_competitions', function (Blueprint $table) {
            $table->id();
            $table->string('FIO');
            $table->string('phone');
            $table->string('email');
            $table->string('stage');
            $table->string('question');
            $table->string('answer');
            $table->time('time',6);
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
        Schema::dropIfExists('result_of_competitions');
    }
}
