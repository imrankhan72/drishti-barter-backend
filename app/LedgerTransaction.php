<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LedgerTransaction extends Model
{
    protected $fillable = ['ledgerTransaction_id','ledgerTransaction_type','ledger_id','transaction_type','amount','transaction_note','transaction_by_id','balance_after_transaction','person_id'];

    protected $dates = ['created_at','updated_at'];


    public function ledgerTransaction()
    {
    	return $this->morphTo();
    }
}
