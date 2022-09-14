<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDrishtreeMitrasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('drishtree_mitras', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('mobile')->unqiue();
            $table->string('email')->unique();
            $table->string('password');
            $table->date('last_password_change')->nullable();
            $table->unsignedBigInteger('ledger_id')->nullable();
            $table->foreign('ledger_id')->references('id')->on('ledgers');
            $table->string('otp')->nullable();
            $table->string('user_type')->default('drishtee_mitras');
            $table->boolean('is_mobile_onboarded')->default(false);
            $table->boolean('status')->default(false);
            $table->unsignedBigInteger('person_id')->nullable();
            $table->foreign('person_id')->references('id')->on('people');
            $table->unsignedBigInteger('added_by')->nullable();
            $table->foreign('added_by')->references('id')->on('users');
            $table->dateTime('added_on')->nullable(); 
            $table->timestamps();
        });

        // Add People, DM Foreign Key Here
        Schema::table('people', function($table) {
            $table->foreign('dm_id')->references('id')->on('drishtree_mitras');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop foreign key before removing the table
        Schema::table('people', function($table) {
            $table->dropForeign(['dm_id']);
        });

        // Now you may drop the table
        Schema::dropIfExists('drishtree_mitras');
    }
}
