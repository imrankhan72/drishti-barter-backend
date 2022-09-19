<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDashboardStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dashboard_stats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->float('vatika',10,2);
            $table->float('mitras',10,2);
            $table->float('persons',10,2);
            $table->float('products',10,2);
            $table->float('completed_barters',10,2);
            $table->float('completed_barter_lp',10,2);
            $table->float('completed_barter_geo',10,2);
            $table->float('open_barters',10,2);
            $table->float('open_barter_lp',10,2);
            $table->float('open_barter_geo',10,2);
            $table->float('tejas_products',10,2);
            $table->float('average_products',10,2);
            $table->float('average_services',10,2);
            $table->float('producers_with_no_product',10,2);
            $table->float('average_no_of_people_with_dm',10,2);
            $table->float('dm_with_no_people',10,2);
            $table->float('producers_with_lp_in_account',10,2);
            $table->float('producers_with_no_lp_in_account',10,2);

            

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
        Schema::dropIfExists('dashboard_stats');
    }
}
