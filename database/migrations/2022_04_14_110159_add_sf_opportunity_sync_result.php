<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSfOpportunitySyncResult extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('salesforce_Opportunity', function (Blueprint $table) {
            $table->timestamp('last_sync');
            $table->integer('sync_result');
            $table->dateTime('last_sync_success');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('salesforce_Opportunity', function (Blueprint $table) {
            $table->dropColumn(['last_sync', 'sync_result', 'last_sync_success']);
        });
    }
}
