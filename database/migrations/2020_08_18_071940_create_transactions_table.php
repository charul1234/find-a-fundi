<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('booking_id');
            $table->string('transaction_type',40)->nullable();
            $table->string('trans_id',40)->nullable();
            $table->string('trans_time',40)->nullable();
            $table->double('trans_amount', 8, 2);
            $table->string('business_shortcode',15)->nullable();
            $table->string('bill_ref_number',40)->nullable();
            $table->string('invoice_number',40)->nullable();
            $table->string('third_party_trans_id',40)->nullable();
            $table->string('msisdn',20)->nullable();
            $table->string('first_name',40)->nullable();
            $table->string('middle_name',40)->nullable();
            $table->string('last_name',40)->nullable();
            $table->double('org_account_balance',8, 2)->nullable();
            $table->string('status',40)->nullable();
            $table->string('payment_mode',20)->nullable();
            $table->unsignedBigInteger('payment_by');


            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('no action');
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('no action');
            $table->foreign('payment_by')->references('id')->on('users')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
