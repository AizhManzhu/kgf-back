<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTelegramRequestLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('telegram_request_logs', function (Blueprint $table) {
            $table->id();
            $table->string('telegram_id')->index()->unique();
            $table->string('message_id')->nullable();
            $table->string('data')->nullable();
            $table->string('command')->nullable();
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
        Schema::dropIfExists('telegram_request_logs');
    }
}
