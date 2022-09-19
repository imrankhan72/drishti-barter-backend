<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDMDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('d_m_devices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('dm_id');
            $table->json('device_properties');
            $table->foreign('dm_id')->references('id')->on('drishtree_mitras');
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
        Schema::dropIfExists('d_m_devices');
    }
}
