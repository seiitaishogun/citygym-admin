<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EditSettingsTbl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //add new column group to table settings
        Schema::table('settings', function (Blueprint $table) {
            $table->string('group', 32)->after('id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //remove column
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['group']);
        });
    }
}
