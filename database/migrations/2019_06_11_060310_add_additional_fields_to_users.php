<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdditionalFieldsToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('mobile_number',45)->nullable()->after('email');
            $table->text('address')->nullable()->after('password');
            $table->string('latitude')->nullable()->after('address');
            $table->string('longitude')->nullable()->after('latitude');
            $table->date('dob')->nullable()->after('longitude');
            $table->time('start_time')->nullable()->after('dob');
            $table->time('end_time')->nullable()->after('start_time');
            $table->tinyInteger('additional_work')->default(0)->after('end_time');
            $table->integer('radius')->default(0)->after('additional_work');
            $table->tinyInteger('is_rfq')->default(0)->after('radius');
            $table->tinyInteger('is_package')->default(0)->after('is_rfq');
            $table->tinyInteger('is_hourly')->default(0)->after('is_package');
            $table->text('profile_picture')->nullable()->after('is_hourly');
            $table->text('facebook_id')->nullable()->after('profile_picture');
            $table->text('facebook_data')->nullable()->after('facebook_id');
            $table->text('google_plus_id')->nullable()->after('facebook_data');
            $table->text('google_plus_data')->nullable()->after('google_plus_id');
            $table->tinyInteger('is_active')->default(1)->after('google_plus_data');
            $table->text('verification_token')->nullable()->after('is_active');
            $table->softDeletes();            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('mobile_number');
            $table->dropColumn('address');
            $table->dropColumn('latitude');
            $table->dropColumn('longitude');
            $table->dropColumn('verification_token');
            $table->dropColumn('profile_picture');
            $table->dropColumn('is_active');
            $table->dropSoftDeletes();
        });
    }
}
