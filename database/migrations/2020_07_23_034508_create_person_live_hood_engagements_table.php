<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePersonLiveHoodEngagementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('person_live_hood_engagements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('live_hood_engagements')->default(false);
            $table->text('description_of_le')->nullable();
            $table->text('group_activity_engagement')->nullable();
            $table->string('type')->nullable();
            $table->string('association')->nullable();
            $table->text('zone')->nullable();
            $table->unsignedBIgInteger('person_id');
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
        Schema::dropIfExists('person_live_hood_engagements');
    }
}
