<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersKpiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_kpi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedDouble('revenue')->nullable()->default(0);
            $table->unsignedDouble('target')->nullable()->default(0);
            $table->unsignedDouble('target_percent')->nullable()->default(0);
            $table->unsignedDouble('convertion_rate')->nullable()->default(0);
            $table->unsignedDouble('rating')->nullable()->default(0);
            $table->unsignedInteger('projection')->nullable()->default(0);
            $table->unsignedInteger('appt_number')->nullable()->default(0);
            $table->unsignedInteger('package_size')->nullable()->default(0);
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
        Schema::dropIfExists('users_kpi');
    }
}
