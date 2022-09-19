<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePersonProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('person_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('geography_id');
            $table->string('geography_type')->nullable();
            $table->unsignedBigInteger('dm_id')->nullable();
            $table->unsignedBigInteger('person_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('unit_id');
            $table->float('quantity_available',10,2);
            $table->float('product_lp',10,2);
            $table->boolean('active_on_barterplace')->default(false);
            $table->foreign('dm_id')->references('id')->on('drishtree_mitras');
            $table->foreign('person_id')->references('id')->on('people');
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('unit_id')->references('id')->on('units');

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
        Schema::dropIfExists('person_products');
    }
}
