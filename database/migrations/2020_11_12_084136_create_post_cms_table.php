<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostCmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //create table for new management
        Schema::create('news', function (Blueprint $table) {
            $table->id('new_id');
            $table->unsignedBigInteger('cat_id')->index();
            $table->string('status',32)->default('published');
            $table->unsignedTinyInteger('is_url_display')->default(0);
            $table->unsignedBigInteger('author_id')->index();
            $table->string('author_type', 255);
            $table->softDeletes();
            $table->timestamps();
        });

        //create news_meta table store the content data
        Schema::create('news_meta', function (Blueprint $table) {
            $table->id('meta_id');
            $table->unsignedBigInteger('new_id')->index();
            $table->string('title',255);
            $table->string('slug',255);
            $table->longText('content')->nullable();
            $table->string('display_url',255)->nullable();
            $table->json('image')->nullable();
            $table->string('lang_code',32);
        });

        //create table for new category
        Schema::create('category', function (Blueprint $table) {
            $table->id('cat_id');
            $table->unsignedBigInteger('parent_id')->index();
            $table->string('status',32)->default('published');
            $table->unsignedBigInteger('author_id')->index();
            $table->string('author_type', 255);
            $table->softDeletes();
            $table->timestamps();
        });

        //create category_meta table store the content data
        Schema::create('category_meta', function (Blueprint $table) {
            $table->id('meta_id');
            $table->unsignedBigInteger('cat_id')->index();
            $table->string('cat_name',255);
            $table->string('slug',255);
            $table->string('lang_code',32);
        });

        //create table for banner
        Schema::create('banner', function (Blueprint $table) {
            $table->id('banner_id');
            $table->string('status',32)->default('published');
            $table->unsignedBigInteger('user_id')->index();
            $table->softDeletes();
            $table->timestamps();
        });

        //create banner_meta table store the content data
        Schema::create('banner_meta', function (Blueprint $table) {
            $table->id('meta_id');
            $table->unsignedBigInteger('banner_id')->index();
            $table->string('title',255)->nullable();
            $table->json('image')->nullable();
            $table->string('lang_code',32);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('news');
        Schema::dropIfExists('news_meta');
        Schema::dropIfExists('category');
        Schema::dropIfExists('category_meta');
        Schema::dropIfExists('banner');
        Schema::dropIfExists('banner_meta');
    }
}
