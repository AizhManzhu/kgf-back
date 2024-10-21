<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPromocodeColumnType extends Migration
{
    public function up()
    {
        Schema::table('promocodes', function (Blueprint $table) {
            $table->string('type')->default(\App\Models\Promocode::SINGLE);
            $table->tinyInteger('has_access')->default(1);
        });
    }

    public function down()
    {
        Schema::table('promocodes', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('has_access');
        });
    }
}
