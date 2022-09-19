<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserProductLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_product_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('mr_no')->unique()->nullable();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('user_product_id');
            $table->float('quantity',10,2)->nullable();
            $table->float('product_lp',10,2)->nullable();
            $table->text('message')->nullable();
            $table->float('amount',10,2)->nullable();
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('user_product_id')->references('id')->on('user_products');
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
        Schema::dropIfExists('user_product_logs');
    }
}
