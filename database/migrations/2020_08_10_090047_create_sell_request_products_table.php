<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSellRequestProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sell_request_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sell_request_id');
            $table->unsignedBigInteger('product_id');
            $table->float('quantity',10,2);
            $table->string('unit');
            $table->float('lp_applicable',10,2);
            $table->foreign('sell_request_id')->references('id')->on('tejas_product_sell_requests');
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
        Schema::dropIfExists('sell_request_products');
    }
}
