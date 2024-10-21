<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsFormatToEventMembers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('event_members', function (Blueprint $table) {
            $table->tinyInteger('format')->default(0);
            $table->tinyInteger('need_mentor')->default(0);
            $table->tinyInteger('is_sponsor')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('event_members', function (Blueprint $table) {
            $table->dropColumn('is_sponsor');
            $table->dropColumn('need_mentor');
            $table->dropColumn('format');
        });
    }
}
