<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBarterNeedServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('barter_need_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('barter_id');
            $table->unsignedBigInteger('service_id');
            $table->float('no_of_days',10,2);
            $table->string('skill_required');
            $table->float('service_lp',10,2);
            $table->foreign('barter_id')->references('id')->on('barters')->onDelete('cascade');
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
        Schema::dropIfExists('barter_need_services');
    }
}
