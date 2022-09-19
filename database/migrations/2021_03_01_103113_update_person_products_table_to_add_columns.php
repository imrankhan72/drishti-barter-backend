<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePersonProductsTableToAddColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('person_products', function (Blueprint $table) {
            $table->float('calc_raw_material_cost',10,2)->nullable();
            $table->float('calc_wage_applicable',10,2)->nullable();
            $table->float('calc_hours_worked',10,2)->nullable();
            $table->float('calc_margin_applicable',10,2)->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('person_products', function (Blueprint $table) {
            $table->dropColumn(['calc_margin_applicable','calc_hours_worked','calc_wage_applicable','calc_raw_material_cost']);
        });
    }
}
