<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBarterNeedProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('barter_need_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('barter_id');
            $table->unsignedBigInteger('product_id');
            $table->float('quantity',10,2);
            $table->float('product_lp',10,2);
            $table->foreign('barter_id')->references('id')->on('barters')->onDelete('cascade');
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
        Schema::dropIfExists('barter_need_products');
    }
}
