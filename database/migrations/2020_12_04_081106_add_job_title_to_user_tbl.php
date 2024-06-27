<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJobTitleToUserTbl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            //add jobTitle
            $table->string('job_title', 128)->nullable()->after('email_verified_at');
            $table->string('sf_account_id', 18)->nullable()->after('job_title');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //remove column
            $table->dropColumn(['job_title', 'sf_account_id']);
        });
    }
}
