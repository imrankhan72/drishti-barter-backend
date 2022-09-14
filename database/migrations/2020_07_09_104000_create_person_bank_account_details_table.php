<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePersonBankAccountDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('person_bank_account_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('payee_name')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->unsignedBigInteger('person_id');
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
        Schema::dropIfExists('person_bank_account_details');
    }
}
