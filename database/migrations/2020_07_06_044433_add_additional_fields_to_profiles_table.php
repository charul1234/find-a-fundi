<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdditionalFieldsToProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->tinyInteger('additional_work')->default(0)->after('dob');
            $table->tinyInteger('is_rfq')->default(0)->after('additional_work');
            $table->tinyInteger('is_package')->default(0)->after('is_rfq');
            $table->tinyInteger('is_hourly')->default(0)->after('is_package');
            $table->float('price',8,2)->nullable()->after('is_hourly');
            $table->time('start_time',0)->nullable()->after('price');
            $table->time('end_time',0)->nullable()->after('start_time');
            $table->text('residential_address')->nullable()->after('instagram_url');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('additional_work');
            $table->dropColumn('is_rfq');
            $table->dropColumn('is_package');
            $table->dropColumn('is_hourly');
            $table->dropColumn('price');
            $table->dropColumn('start_time');
            $table->dropColumn('end_time');
            $table->dropColumn('residential_address');
        });
    }
}
