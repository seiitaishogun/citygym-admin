<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesforceRecordTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salesforce_Record_Type', function (Blueprint $table) {
            $table->string('Id', 64)->index()->primary();
            $table->string('Name', 255)->nullable();
            $table->string('DeveloperName', 255)->nullable();
            $table->string('SobjectType', 255)->nullable()->index();
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
        Schema::dropIfExists('salesforce_Record_Type');
    }
}
