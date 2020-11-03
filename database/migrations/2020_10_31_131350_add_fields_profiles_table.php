<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    { 
        Schema::table('profiles', function (Blueprint $table) {            
            $table->string('zip_code',15)->nullable()->after('security_check');
            $table->string('address_line_1')->nullable()->after('zip_code');
            $table->tinyInteger('is_academy_trained')->default(0)->after('address_line_1');
            $table->date('academy_year')->nullable()->after('is_academy_trained');
            $table->string('personal_admin_remarks')->nullable()->after('academy_year');
            $table->string('personal_admin_rating',10)->default(0)->nullable()->after('personal_admin_remarks');
            $table->string('technical_admin_remarks')->nullable()->after('personal_admin_rating');
            $table->string('technical_admin_rating',10)->default(0)->nullable()->after('technical_admin_remarks');
            $table->tinyInteger('is_personal_verified')->default(0)->after('technical_admin_rating');
            $table->tinyInteger('is_technical_verified')->default(0)->after('is_personal_verified');
            $table->string('reference_name1',45)->nullable()->after('is_technical_verified');
            $table->string('reference_mobile_number1',45)->nullable()->after('reference_name1');
            $table->string('reference_name2',45)->nullable()->after('reference_mobile_number1');
            $table->string('reference_mobile_number2',45)->nullable()->after('reference_name2');
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
          $table->dropColumn('zip_code');
          $table->dropColumn('address_line_1');
          $table->dropColumn('is_academy_trained');
          $table->dropColumn('academy_year');
          $table->dropColumn('personal_admin_remarks');
          $table->dropColumn('personal_admin_rating');
          $table->dropColumn('technical_admin_remarks');
          $table->dropColumn('technical_admin_rating');
          $table->dropColumn('is_personal_verified');
          $table->dropColumn('is_technical_verified');  
          $table->dropColumn('reference_name1'); 
          $table->dropColumn('reference_mobile_number1'); 
          $table->dropColumn('reference_name2'); 
          $table->dropColumn('reference_mobile_number2');         
        });
    }
}
