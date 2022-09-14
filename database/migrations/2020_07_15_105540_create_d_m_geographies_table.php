<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDMGeographiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('d_m_geographies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('dm_id')->nullable();
            $table->foreign('dm_id')->references('id')->on('drishtree_mitras');
            $table->unsignedBigInteger('geography_id')->refrences('id')->on('geographies');
            $table->unsignedBigInteger('added_by')->nullable();
            $table->foreign('added_by')->references('id')->on('users');
            $table->dateTime('added_on')->nullable();
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
        Schema::dropIfExists('d_m_geographies');
    }
}
