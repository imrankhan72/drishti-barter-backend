<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBarterHaveProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('barter_have_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('barter_id')->nullable();
            $table->unsignedBigInteger('person_product_id')->nullable();
            $table->float('quantity',10,2);
            $table->float('product_lp',10,2);
            $table->string('quantity_unit');
            $table->foreign('barter_id')->references('id')->on('barters')->onDelete('cascade');
            $table->foreign('person_product_id')->references('id')->on('person_products');
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
        Schema::dropIfExists('barter_have_products');
    }
}
