<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterBookingUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('booking_users');
        Schema::create('booking_users', function (Blueprint $table) {    
            $table->bigIncrements('id');        
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('booking_id');
            $table->tinyInteger('is_rfq')->default(0);
            $table->float('budget', 8, 2)->nullable();
            $table->datetime('service_datetime')->nullable();
            $table->text('requirement')->nullable();
            $table->tinyInteger('is_quoted')->default(0);
            $table->string('status', 45)->nullable();
            $table->text('reason')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('booking_users');
    }
}
