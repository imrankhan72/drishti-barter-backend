<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTejasProductBuyFromPeopleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tejas_product_buy_from_people', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('buy_request_id');
            $table->unsignedBigInteger('product_id');
            $table->float('quantity',10,2);
            $table->string('unit');
            $table->float('lp_applicable',10,2);
            $table->foreign('buy_request_id')->references('id')->on('tejas_request_buy_from_people');
            $table->foreign('product_id')->references('id')->on('products');
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
        Schema::dropIfExists('tejas_product_buy_from_people');
    }
}
