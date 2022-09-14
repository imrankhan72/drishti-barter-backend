<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePersonBansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('person_bans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('requester_id');
            $table->unsignedBigInteger('person_id');
            $table->unsignedBigInteger('approver_id')->nullable();
            $table->string('status')->default('Open');
            $table->string('comment')->nullable();
            $table->foreign('approver_id')->references('id')->on('users');
            $table->foreign('requester_id')->references('id')->on('drishtree_mitras');
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
        Schema::dropIfExists('person_bans');
    }
}
