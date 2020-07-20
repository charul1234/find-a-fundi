<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddYearofexperienceFieldProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('profiles', function (Blueprint $table) {
           $table->text('year_experience')->defaul(0)->nullable()->after('passport_number')->comment('Year of Experience');
           $table->text('reference')->defaul(0)->nullable()->after('year_experience')->comment('reference');
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
           $table->dropColumn('year_experience');
           $table->dropColumn('reference');
        });
    }
}
