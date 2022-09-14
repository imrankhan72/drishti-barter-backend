<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->float('default_livelihood_points',10,2)->nullable();
            $table->unsignedBigInteger('service_category_id');
            $table->unsignedBigInteger('added_by_user_id');
            $table->integer('availability')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->string('icon_name')->nullable();
            $table->string('icon_path')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->foreign('service_category_id')->references('id')->on('service_categories');
            $table->foreign('approved_by')->references('id')->on('users'); 
            // $table->float('skill_level',10,2);
            // $table->float('applicable_time',10,2);
            $table->softDeletes();
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
        Schema::dropIfExists('services');
    }
}
