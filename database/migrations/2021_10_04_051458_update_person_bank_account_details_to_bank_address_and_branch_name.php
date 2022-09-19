<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePersonBankAccountDetailsToBankAddressAndBranchName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('person_bank_account_details', function (Blueprint $table) {
            $table->string('branch_name')->nullable();
            $table->text('branch_address')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('person_bank_account_details', function (Blueprint $table) {
            $table->dropColumn(['branch_address','branch_name']);
        });
    }
}
