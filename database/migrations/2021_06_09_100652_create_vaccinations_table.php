<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVaccinationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vaccinations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('reg_id')->nullable();
            $table->unsignedBigInteger('added_by');
            $table->unsignedBigInteger('person_id')->unique();
            $table->unsignedBigInteger('geography_id');
            $table->string('vaccine_name');
            $table->string('gender')->nullable();
            $table->string('dose_1_place')->nullable();
            $table->string('dose_2_place')->nullable();
            $table->dateTime('dose_1_date')->nullable();
            $table->dateTime('dose_2_date')->nullable();
            $table->boolean('is_dose_1')->default(false);
            $table->boolean('is_dose_2')->default(false);
            $table->text('dose_1_certificate_name')->nullable();
            $table->text('dose_1_certificate_path')->nullable();
            $table->text('dose_2_certificate_name')->nullable();
            $table->text('dose_2_certificate_path')->nullable();
            $table->boolean('dose_1_complete')->default(false);
            $table->boolean('dose_2_complete')->default(false);
            $table->foreign('added_by')->references('id')->on('drishtree_mitras');
            $table->foreign('person_id')->references('id')->on('people');
            $table->foreign('geography_id')->references('id')->on('geographies');
            

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vaccinations');
    }
}
