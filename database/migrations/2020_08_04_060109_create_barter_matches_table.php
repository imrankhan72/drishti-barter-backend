<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBarterMatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('barter_matches', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('barter_id');
            $table->string('match_type');
            $table->unsignedBigInteger('barter_2_id')->nullable();
            $table->unsignedBigInteger('person_id');
            $table->float('total_lp_offered',10,2);
            $table->string('local_inventory_type');
            $table->foreign('barter_id')->references('id')->on('barters')->onDelete('cascade');
            $table->foreign('person_id')->references('id')->on('people');
            $table->foreign('barter_2_id')->references('id')->on('barters')->onDelete('cascade');
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
        Schema::dropIfExists('barter_matches');
    }
}
