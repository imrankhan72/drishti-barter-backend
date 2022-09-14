<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('name');
            $table->float('default_livehood_points',10,2)->nullable();
            $table->unsignedBigInteger('product_category_id');
            $table->float('calc_raw_material_cost',10,2)->nullable();
            $table->float('calc_hours_worked',10,2)->nullable();
            $table->float('calc_wage_applicable',10,2)->nullable();
            $table->float('calc_margin_applicable',10,2)->nullable();
            $table->integer('added_by_user_id')->nullable();
            $table->boolean('is_gold_product')->default(false);
            $table->boolean('is_branded_product')->default(false);
            $table->float('mrp',10,2)->nullable();
            $table->integer('availability')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->text('photo_path')->nullable();
            $table->string('photo_name')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->softDeletes();
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->foreign('product_category_id')->references('id')->on('product_categories');
            $table->foreign('approved_by')->references('id')->on('users');
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
        Schema::dropIfExists('products');
    }
}
