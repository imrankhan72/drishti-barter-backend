<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLpRequestFromAdminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lp_request_from_admins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('admin_id');
            $table->float('points_needed',10,2);
            $table->unsignedBigInteger('superadmin_approver_id')->nullable();
            $table->string('status')->default('Open');
            $table->foreign('admin_id')->references('id')->on('users');
            $table->foreign('superadmin_approver_id')->references('id')->on('users');
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
        Schema::dropIfExists('lp_request_from_admins');
    }
}
