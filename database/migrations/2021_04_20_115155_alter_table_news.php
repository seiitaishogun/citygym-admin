<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableNews extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //add news type
        Schema::table('news', function (Blueprint $table) {
            $table->string('news_type', 128)->after('author_type')->nullable()->default('news');
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
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn(['news_type']);
        });
    }
}
