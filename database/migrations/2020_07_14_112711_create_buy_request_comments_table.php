<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBuyRequestCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('buy_request_comments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('buy_request_id');
            $table->unsignedBigInteger('commentor_id');
            $table->text('comment');
            $table->dateTime('date_time');
            $table->foreign('buy_request_id')->references('id')->on('tejas_product_buy_requests');
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
        Schema::dropIfExists('buy_request_comments');
    }
}
