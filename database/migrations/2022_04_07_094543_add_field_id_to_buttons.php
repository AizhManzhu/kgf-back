<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldIdToButtons extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('buttons', function (Blueprint $table) {
            $table->foreignId('field_id')->after('text')->nullable()->references('id')->on('fields')->onDeleteIndex();
            $table->foreignId('preparedtext_id')->after('field_id')->nullable()->references('id')->on('preparedtexts')->onDeleteIndex();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('buttons', function (Blueprint $table) {

        });
    }
}
