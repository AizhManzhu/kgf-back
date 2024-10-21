<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMemberTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable()->comment('Имя');
            $table->string('last_name')->nullable()->comment('Фамилия');
            $table->string('username')->nullable()->comment('Юзернэйм');
            $table->string('telegram_id')->index()->nullable()->comment('Телеграм айди на которую отправляем сообщения');
            $table->string('phone')->nullable()->comment('Номер телефона');
            $table->string('company')->nullable()->comment('Компания');
            $table->string('position')->nullable()->comment('Должность');
            $table->string('email')->nullable()->comment('Почтовый адрес');
            $table->smallInteger('is_checked')->default(0)->comment('подтвержденный ли пользователь');
            $table->smallInteger('is_active')->default(0)->comment('Подтверждение на рассылку');
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
        Schema::dropIfExists('members');
    }
}
