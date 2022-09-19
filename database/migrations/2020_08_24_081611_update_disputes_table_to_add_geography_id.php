<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateDisputesTableToAddGeographyId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('disputes', function (Blueprint $table) {
            $table->unsignedBigInteger('geography_id')->nullable();
            $table->foreign('geography_id')->references('id')->on('geographies')->onDelete('cascade');
        });
    }

    /**


     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('disputes', function (Blueprint $table) {
         $table->dropForeign('geography_id');
         $table->dropColumn(['geography_id']);
        });
    }
}
