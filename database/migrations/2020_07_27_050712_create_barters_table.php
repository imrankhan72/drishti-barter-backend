<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBartersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('barters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('person_id');
            $table->unsignedBigInteger('added_by_dm_id');
            $table->dateTime('barter_date_time_added');
            $table->unsignedBigInteger('geography_id');
            $table->string('geography_type')->nullable();
            $table->integer('is_barter_expire')->default(false);
            $table->dateTime('barter_expire_date')->nullable();
            $table->string('status')->default('Open');
            $table->float('barter_total_lp_offered',10,2);
            $table->float('barter_total_lp_needed',10,2);
            $table->boolean('is_disputed')->default(false);
            $table->foreign('person_id')->references('id')->on('people');
            $table->foreign('added_by_dm_id')->references('id')->on('drishtree_mitras');
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
        Schema::dropIfExists('barters');
    }
}
