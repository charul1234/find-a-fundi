<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schedules', function (Blueprint $table) {
              $table->unsignedBigInteger('user_id')->after('booking_id');
              $table->string('service_title', 45)->nullable()->after('end_time');
              $table->text('requirements')->nullable()->after('service_title');
              $table->float('price', 8, 2)->nullable()->after('task');
              $table->tinyInteger('is_verify')->default(0)->after('status');
              $table->unsignedBigInteger('verified_by')->default(0)->after('is_verify');
              $table->foreign('user_id')->references('id')->on('users')->onDelete('no action');
              $table->dropColumn('task'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->dropColumn('service_title');
            $table->dropColumn('requirements');
            $table->dropColumn('price');
            $table->dropColumn('is_verify');
            $table->dropColumn('verified_by');   
            $table->text('task')->nullable()->after('end_time');         
        });
    }
}
