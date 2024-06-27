<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTableCountryward extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //add new colum Id to table country_ward
        Schema::table('country_ward', function (Blueprint $table) {
            //add username column
            $table->string('sf_id', 64)->after('name')->index();

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
        Schema::table('country_ward', function (Blueprint $table) {
            $table->dropColumn(['sf_id']);
        });
    }
}
