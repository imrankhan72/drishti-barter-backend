<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePersonIntrastructureDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('person_intrastructure_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('total_land_holding')->nullable();
            $table->boolean('irrigation_facilities')->default(false);
            $table->integer('cultivable_land')->nullable();
            $table->boolean('crop_mapping')->nullable();
            $table->text('livestock')->nullable();
            $table->string('house_type')->nullable();
            $table->text('vehicles')->nullable();
            $table->boolean('own_house')->default(false);
            $table->integer('storage_space')->nullable();
            $table->text('construction_material')->nullable();
            $table->text('machines')->nullable();
            $table->text('farming_equipment')->nullable();
            $table->unsignedBigInteger('person_id');
            $table->foreign('person_id')->references('id')->on('people');
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
        Schema::dropIfExists('person_intrastructure_details');
    }
}
