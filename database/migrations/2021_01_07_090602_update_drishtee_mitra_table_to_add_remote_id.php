<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateDrishteeMitraTableToAddRemoteId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('drishtree_mitras', function (Blueprint $table) {
            $table->unsignedBigInteger('remote_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('drishtree_mitras', function (Blueprint $table) {
            $table->dropColumn(['remote_id']);
        });
    }
}
