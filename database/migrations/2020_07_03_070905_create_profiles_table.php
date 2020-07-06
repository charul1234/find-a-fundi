<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->default(0);
            $table->unsignedBigInteger('city_id')->nullable();
            $table->unsignedBigInteger('experience_level_id')->nullable();
            $table->unsignedBigInteger('payment_option_id')->nullable();
            $table->date('dob')->nullable();
            $table->text('facebook_url')->nullable();
            $table->text('twitter_url')->nullable();
            $table->text('linkedin_url')->nullable();
            $table->text('googleplus_url')->nullable();
            $table->text('instagram_url')->nullable();
            $table->text('work_address')->nullable();
            $table->integer('radius')->defaul(0)->nullable();
            $table->string('latitude',45)->nullable();
            $table->string('longitude',45)->nullable();
            $table->string('passport_number')->nullable();
            $table->tinyInteger('display_seeker_reviews')->default(0);
            $table->tinyInteger('fundi_is_middlemen')->default(0);
            $table->tinyInteger('fundi_have_tools')->default(0);
            $table->tinyInteger('fundi_have_smartphone')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); 
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('set null'); 
            $table->foreign('experience_level_id')->references('id')->on('experience_levels')->onDelete('set null'); 
            $table->foreign('payment_option_id')->references('id')->on('payment_options')->onDelete('set null'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('profiles');
    }
}
