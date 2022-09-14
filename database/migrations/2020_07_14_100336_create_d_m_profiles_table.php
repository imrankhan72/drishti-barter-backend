<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDMProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('d_m_profiles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->dateTime('dob')->nullable();
            $table->text('address')->nullable();
            $table->string('photo_name')->nullable();
            $table->text('photo_path')->nullable();
            $table->string('whatsapp_no')->nullable();
            $table->string('alternative_mobile_no')->nullable();
            $table->unsignedBigInteger('dm_id')->nullable();
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
        Schema::dropIfExists('d_m_profiles');
    }
}
