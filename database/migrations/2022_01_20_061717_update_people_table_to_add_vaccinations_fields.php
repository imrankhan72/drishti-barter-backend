<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePeopleTableToAddVaccinationsFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('people', function (Blueprint $table) {
            $table->boolean('vaccinated')->default(false);
            $table->string('dose_1_certificate_name')->nullable();
            $table->text('dose_1_certificate_path')->nullable();
            $table->string('dose_2_certificate_name')->nullable();
            $table->text('dose_2_certificate_path')->nullable();
            $table->string('precation_dose_certificate_name')->nullable();
            $table->text('precaution_dose_certificate_path')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('people', function (Blueprint $table) {
           $table->dropColumn(['vaccinated','dose_1_certificate_name','dose_1_certificate_path','dose_2_certificate_name','dose_2_certificate_path','precation_dose_certificate_name','precaution_dose_certificate_path']);
        });
    }
}
