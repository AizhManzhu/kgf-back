<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTablePreparedtexts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('preparedtexts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Название шаблона');
            $table->text('text');
            $table->string('image')->nullable();
            $table->foreignId('event_id')->reference('events')->index()->nullable();
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
        Schema::dropIfExists('preparedtexts');
    }
}
