<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePersonKycDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('person_kyc_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('is_kyc_done')->default(false);
            $table->string('adhar_card_no')->nullable();
            $table->text('adhar_card_photo_name')->nullable();
            $table->text('adhar_card_photo_path')->nullable();
            $table->string('pancard_no')->nullable();
            $table->text('pancard_photo_name')->nullable();
            $table->text('pancard_photo_path')->nullable();
            $table->string('dl_no')->nullable();
            $table->text('dl_photo_name')->nullable();
            $table->text('dl_photo_path')->nullable();
            $table->string('passport_no')->nullable();
            $table->string('passport_photo_name')->nullable();
            $table->text('passport_photo_path')->nullable();
            $table->string('voter_id_no')->nullable();
            $table->string('voter_id_photo_name')->nullable();
            $table->string('voter_id_photo_path')->nullable();
            
            $table->unsignedBigInteger('person_id');
            $table->foreign('person_id')->references('id')->on('people'); 
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
        Schema::dropIfExists('person_kyc_details');
    }
}
