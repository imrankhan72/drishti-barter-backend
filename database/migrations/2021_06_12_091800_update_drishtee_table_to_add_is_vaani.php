<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateDrishteeTableToAddIsVaani extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('drishtree_mitras', function (Blueprint $table) {
            $table->boolean('is_vaani')->default(false);
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
            $table->dropColumn(['is_vaani']);
        });
    }
}
