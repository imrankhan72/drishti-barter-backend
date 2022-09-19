<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateVaccinationsTableToAddCertificateUploadDate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vaccinations', function (Blueprint $table) {
            $table->dateTime('certificate_dose_1_upload_date')->nullable();
            $table->dateTime('certificate_dose_2_upload_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vaccinations', function (Blueprint $table) {
            $table->dropColumn(['certificate_dose_1_upload_date','certificate_dose_2_upload_date']);
        });
    }
}
