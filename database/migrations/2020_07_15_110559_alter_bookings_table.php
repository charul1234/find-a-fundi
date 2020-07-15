<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['package_id']);
            $table->dropColumn('package_id');
            $table->unsignedBigInteger('category_id')->after('id');
            $table->unsignedBigInteger('user_id')->after('category_id');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade'); 
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('package_id')->after('id');
            $table->foreign('package_id')->references('id')->on('packages')->onDelete('cascade'); 
            $table->dropForeign(['category_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn('category_id');
            $table->dropColumn('user_id');
        });
    }
}
