<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_log', function (Blueprint $table) {
            $table->id();
            $table->float('duration')->default(0);
            $table->integer('status_code')->default(0);
            $table->string('url', 3000)->nullable();
            $table->string('method', 255)->nullable();
            $table->string('ip', 255)->nullable();
            $table->longText('Request')->nullable();
            $table->longText('Response')->nullable();
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
        Schema::dropIfExists('request_log');
    }
}
