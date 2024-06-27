<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableBannerMeta extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //add news type
        Schema::table('banner_meta', function (Blueprint $table) {
            $table->string('link_type', 32)->after('title')->nullable()->nullable();
            $table->string('display_url', 255)->after('link_type')->nullable()->nullable();
            $table->text('content')->nullable()->after('display_url');
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
        Schema::table('banner_meta', function (Blueprint $table) {
            $table->dropColumn(['link_type', 'display_url', 'content']);
        });
    }
}
