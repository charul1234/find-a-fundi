<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveForeignUseridUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
             $table->dropForeign(['user_id']);
             $table->dropColumn('user_id');
        });
        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->after('category_id');
        });
       
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
                               
    }
}
