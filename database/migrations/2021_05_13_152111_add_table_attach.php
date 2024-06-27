<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTableAttach extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //add table attach
        Schema::create('attach', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->index();
            $table->string('file_name')->nullable();
            $table->string('file_title')->nullable();
            $table->string('file_type')->nullable();
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
        //drop table
        Schema::dropIfExists('attach');
    }
}
