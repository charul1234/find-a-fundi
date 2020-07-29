<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldsCommentBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
           $table->text('comment')->nullable()->after('requirement');
        });
        Schema::table('booking_users', function (Blueprint $table) { 
           $table->text('comment')->nullable()->after('requirement'); 
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
            $table->dropColumn('comment');
        });
        Schema::table('booking_users', function (Blueprint $table) {
            $table->dropColumn('comment');
        });
    }
}
