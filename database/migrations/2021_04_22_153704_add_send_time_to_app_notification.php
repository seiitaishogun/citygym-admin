<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSendTimeToAppNotification extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('app_notification', function (Blueprint $table) {
            $table->dateTime('send_time')->nullable()->after('is_seen');
            $table->tinyInteger('is_sent')->after('send_time')->default(0);
            $table->text('data_option')->nullable()->after('content');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('app_notification', function (Blueprint $table) {
            $table->dropColumn(['send_time', 'is_sent', 'data_option']);
        });
    }
}
