<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBarterMatchLocalInventoryServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('barter_match_local_inventory_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('barter_id');
            $table->unsignedBigInteger('barter_match_id');
            $table->unsignedBigInteger('service_id');
            $table->float('no_of_days');
            $table->float('service_lp');
            $table->foreign('barter_id')->references('id')->on('barters')->onDelete('cascade');
            $table->foreign('barter_match_id')->references('id')->on('barter_matches')->onDelete('cascade');
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
        Schema::dropIfExists('barter_match_local_inventory_services');
    }
}
