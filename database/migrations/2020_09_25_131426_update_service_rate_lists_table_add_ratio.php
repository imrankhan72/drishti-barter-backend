<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateServiceRateListsTableAddRatio extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_rate_lists', function (Blueprint $table) {
            $table->float('professionals_ratio',10,2)->nullable();
            $table->float('highly_skilled_ratio')->nullable();
            $table->float('skilled_ratio')->nullable();
            $table->float('semi_skilled_ratio')->nullable();
            $table->float('onskilled_ratio')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_rate_lists', function (Blueprint $table) {
             $table->dropColumn(['professionals_ratio','highly_skilled_ratio','skilled_ratio','semi_skilled_ratio','onskilled_ratio']);
             });
    }
}
