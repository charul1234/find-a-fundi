<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('package_id');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->text('location')->nullable();
            $table->string('latitude',45)->nullable();
            $table->string('longitude',45)->nullable();
            $table->float('budget', 8, 2)->nullable();
            $table->tinyInteger('is_rfq')->default(0);
            $table->float('request_for_quote_budget', 8, 2)->nullable();
            $table->tinyInteger('is_hourly')->default(0);
            $table->integer('estimated_hours')->default(0)->nullable();
            $table->float('min_budget', 8, 2)->nullable();
            $table->float('max_budget', 8, 2)->nullable();
            $table->tinyInteger('is_package')->default(0);
            $table->integer('quantity')->default(0)->nullable();
            $table->datetime('datetime')->nullable();
            $table->datetime('service_datetime')->nullable();
            $table->text('requirement')->nullable();
            $table->string('status', 45)->nullable();
            $table->timestamps();

            $table->foreign('package_id')->references('id')->on('packages')->onDelete('cascade'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bookings');
    }
}
