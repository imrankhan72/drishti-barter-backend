<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSellRequestCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sell_request_comments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sell_request_id');
            $table->unsignedBigInteger('commentor_id');
            $table->text('comment');
            $table->dateTime('date_time');
            $table->foreign('sell_request_id')->references('id')->on('tejas_product_sell_requests');
            $table->foreign('commentor_id')->references('id')->on('users');
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
        Schema::dropIfExists('sell_request_comments');
    }
}
