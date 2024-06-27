<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppNotificationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_notification', function (Blueprint $table) {
            $table->id();
            $table->string('name', 32)->index();
            $table->bigInteger('user_id')->index();
            $table->string('group', 32)->nullable();
            $table->string('title', 255)->nullable();
            $table->text('message')->nullable();
            $table->text('content')->nullable();
            $table->tinyInteger('is_seen', false,true)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('app_notification');
    }
}
