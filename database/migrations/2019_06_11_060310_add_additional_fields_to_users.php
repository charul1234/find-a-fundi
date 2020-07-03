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
            $table->tinyInteger('display_seeker_reviews')->default(0)->after('mobile_number');
            $table->text('facebook_id')->nullable()->after('display_seeker_reviews');
            $table->text('facebook_data')->nullable()->after('facebook_id');
            $table->text('google_plus_id')->nullable()->after('facebook_data');
            $table->text('google_plus_data')->nullable()->after('google_plus_id');
            $table->tinyInteger('is_active')->default(0)->after('google_plus_data');
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
            $table->dropColumn('facebook_id');
            $table->dropColumn('facebook_data');
            $table->dropColumn('google_plus_id');
            $table->dropColumn('google_plus_data');
            $table->dropColumn('is_active');
            $table->dropSoftDeletes();
        });
    }
}
