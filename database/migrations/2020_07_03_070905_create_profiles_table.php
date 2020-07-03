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
            $table->unsignedBigInteger('country_id')->default(0);
            $table->unsignedBigInteger('city_id')->default(0);
            $table->unsignedBigInteger('experience_level_id')->default(0);
            $table->unsignedBigInteger('payment_option_id')->default(0);
            $table->date('dob')->nullable();
            $table->text('facebook_url')->nullable();
            $table->text('twitter_url')->nullable();
            $table->text('linkedin_url')->nullable();
            $table->text('googleplus_url')->nullable();
            $table->text('instagram_url')->nullable();
            $table->text('work_address')->nullable();
            $table->integer('radius')->nullable();
            $table->string('latitude',45)->nullable();
            $table->string('longitude',45)->nullable();
            $table->string('passport_number')->nullable();
            $table->tinyInteger('fundi_is_middlemen')->default(0);
            $table->tinyInteger('fundi_have_tools')->default(0);
            $table->tinyInteger('fundi_have_smartphone')->default(0);
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
        Schema::dropIfExists('profiles');
    }
}
