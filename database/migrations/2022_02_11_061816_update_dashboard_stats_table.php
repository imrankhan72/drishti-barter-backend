<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateDashboardStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dashboard_stats', function (Blueprint $table) {
            $table->float('csp_count',10,2)->nullable();
            $table->float('mitra_count',10,2)->nullable();
            $table->float('ceep_count',10,2)->nullable();
            $table->float('others_count',10,2)->nullable();
            $table->float('vaccinated',10,2)->nullable();
            $table->float('d1_vaccinated',10,2)->nullable();
            $table->float('d2_vaccinated',10,2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dashboard_stats', function (Blueprint $table) {
            $table->dropColumn(['csp_count','mitra_count','ceep_count','others_count','vaccinated','d1_vaccinated','d2_vaccinated']);
        });
    }
}
