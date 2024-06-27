<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserProfile extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //add new profile's colum to user table
        Schema::table('users', function (Blueprint $table) {
            //add user profile columns
            $table->string('nick_name', 128)->after('name')->nullable();
            $table->string('phone', 20)->after('nick_name')->nullable();
            $table->date('dob')->after('phone')->nullable();
            $table->unsignedTinyInteger('gender')->after('dob')->nullable();
            $table->string('club_id', 18)->after('gender')->nullable();
            $table->text('address')->nullable()->after('club_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //remove column
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nick_name', 'phone', 'dob', 'gender','club_id', 'address']);
        });
    }
}
