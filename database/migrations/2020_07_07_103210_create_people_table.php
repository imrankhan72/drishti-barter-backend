<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePeopleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('people', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->unqiue()->nullable();
            $table->string('mobile')->unqiue();
            $table->unsignedBigInteger('geography_id');
            $table->string('geography_type');
            $table->unsignedBigInteger('ledger_id')->nullable();
            $table->string('status')->default('Active');
            $table->string('otp')->nullable();
            $table->foreign('geography_id')->references('id')->on('geographies');
            $table->foreign('ledger_id')->references('id')->on('ledgers');
            $table->unsignedBigInteger('dm_id')->nullable();
            // This is incorrect, drishtee_mitras table doesn't exist right now
            // $table->foreign('dm_id')->references('id')->on('drishtree_mitras');
            $table->unsignedBigInteger('add_by_user_id')->nullable();
            $table->foreign('add_by_user_id')->references('id')->on('users');
            $table->dateTime('added_on')->nullable();
            $table->boolean('is_profile_compelete')->default(false);
            $table->softDeletes();
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
        Schema::dropIfExists('people');
    }
}
