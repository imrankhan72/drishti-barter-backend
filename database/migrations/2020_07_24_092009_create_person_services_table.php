<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePersonServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('person_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('geography_id');
            $table->string('geography_type')->nullable();
            $table->unsignedBigInteger('dm_id')->nullable();
            $table->unsignedBigInteger('person_id')->nullable();
            $table->unsignedBigInteger('service_id')->nullable();
            $table->float('service_lp',10,2)->nullable();
            $table->string('skill_level');
            $table->boolean('active_on_barterplace')->default(false);
            $table->foreign('dm_id')->references('id')->on('drishtree_mitras');
            $table->foreign('person_id')->references('id')->on('people');
            $table->foreign('service_id')->references('id')->on('services');

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
        Schema::dropIfExists('person_services');
    }
}
