<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePersonIncomesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('person_incomes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('income_type')->nullable();
            $table->string('monthly_income')->nullable();
            $table->boolean('bpl_card_holder')->default(false);
            $table->unsignedBIgInteger('person_id');
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
        Schema::dropIfExists('person_incomes');
    }
}
