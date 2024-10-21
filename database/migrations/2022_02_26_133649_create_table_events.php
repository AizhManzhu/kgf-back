<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableEvents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable()->comment('Название мероприятия');
            $table->string('description', 1000)->nullable()->comment('Краткое описание');
            $table->string('address')->nullable()->comment('Адрес');
            $table->dateTime('event_date')->nullable()->comment('Дата начало');
            $table->tinyInteger('is_current')->index()->default(0);
            $table->string('image')->nullable();
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
        Schema::dropIfExists('events');
    }
}
