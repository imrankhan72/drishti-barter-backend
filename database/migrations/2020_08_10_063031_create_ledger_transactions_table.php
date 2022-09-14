<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLedgerTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ledger_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('ledger_id');
            $table->unsignedBigInteger('ledgerTransaction_id');
            $table->string('transaction_type');
            $table->float('amount',10,2);
            $table->text('transaction_note');
            // $table->unsignedBigInteger('transaction_by_id')->nullable();
            $table->string('ledgerTransaction_type');
            $table->float('balance_after_transaction',10,2);
            $table->foreign('ledger_id')->references('id')->on('ledgers')->onDelete('cascade');       
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
        Schema::dropIfExists('ledger_transactions');
    }
}
