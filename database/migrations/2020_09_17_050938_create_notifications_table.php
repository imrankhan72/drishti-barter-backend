<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('dm_id');
            $table->string('title')->nullable();
            $table->string('body')->nullable();
            $table->string('icon')->nullable();
            $table->string('device_token')->nullable();
            $table->string('notification_device')->nullable();
            $table->string('click_action')->nullable();
            $table->dateTime('read_at')->nullable();
            $table->softDeletes();
            $table->foreign('dm_id')->references('id')->on('drishtree_mitras');
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
        Schema::dropIfExists('notifications');
    }
}
