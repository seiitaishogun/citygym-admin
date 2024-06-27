<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // add more employee to user table
        DB::statement("ALTER TABLE users MODIFY COLUMN 	type ENUM('admin', 'user', 'employee')");

        //add new colum to user table
        Schema::table('users', function (Blueprint $table) {
            //add username column
            $table->string('username', 64)->after('type');
            //add force_pass_reset column
            $table->tinyInteger('force_pass_reset')->default(0)->after('password');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //remove enum type
        DB::statement("ALTER TABLE users MODIFY COLUMN 	type ENUM('admin', 'user')");

        //remove column
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'force_pass_reset']);
        });
    }
}
