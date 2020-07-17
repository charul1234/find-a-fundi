<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterRadiusFieldProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('profiles', function (Blueprint $table) {
           $table->dropColumn('radius');
        });
        Schema::table('profiles', function (Blueprint $table) {
           $table->float('radius', 8, 2)->nullable()->after('work_address')->comment('KM');
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
           $table->dropColumn('radius');
        });
        Schema::table('profiles', function (Blueprint $table) {
           $table->integer('radius')->defaul(0)->nullable()->after('work_address')->comment('KM');
        });
    }
}
