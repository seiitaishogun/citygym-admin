<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterRoleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // add more employee role to roles table
        DB::statement("ALTER TABLE roles MODIFY COLUMN type ENUM('admin', 'user', 'employee')");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //remove enum type
        DB::statement("ALTER TABLE roles MODIFY COLUMN type ENUM('admin', 'user')");
    }
}
