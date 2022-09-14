<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTejasRequestSellToPeopleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tejas_request_sell_to_people', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('requester_person_id');
            $table->unsignedBigInteger('person_id');
            $table->string('status')->default('Open');
            $table->foreign('person_id')->references('id')->on('people');
            $table->foreign('requester_person_id')->references('id')->on('people');
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
        Schema::dropIfExists('tejas_request_sell_to_people');
    }
}
