<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\LedgerTransaction;

class TejasRequestSellToPeople extends Model
{
    protected $fillable = ['requester_person_id','person_id','status'];

    protected $dates = ['created_at','updated_at'];

    public function requester_person()
    {
    	return $this->belongsTo('App\Person','requester_person_id');
    }

    public function person()
    {
        return $this->belongsTo('App\Person','person_id');
    }
    
    public function sellToPersonProducts(){
        return $this->hasMany('App\TejasProductSellToPerson','sell_request_id','id');
    }

    public function ledgerTransactions()
    {
        return $this->morphMany(LedgerTransaction::class, 'ledgerTransaction');
    }

    /**
     *
     * @param  $type Success/Fail
     * @param  $ledger_id of Person
     * @param  $transaction_type of transaction credit/debit
     * @param  $amount transaction amount
     * @param  $note comment
     * @param  $balance_after_transaction after transaction remainig balance
     * @return true
     * do store Person Ledger transaction
     */
    public function createTejasSellLedgerTransactions($type,$ledger_id,$transaction_type,$amount,$note,$balance_after_transaction,$person_id){
     $ledger_tran = new LedgerTransaction();
      $ledger_tran->person_id = $person_id;
      $ledger_tran->ledger_id = $ledger_id;
      $ledger_tran->transaction_type = $transaction_type;
      $ledger_tran->amount = $amount;
      $ledger_tran->transaction_note = $note;
      $ledger_tran->ledgerTransaction_type = $type;
      $ledger_tran->balance_after_transaction = $balance_after_transaction;
      // $log->user_id = $user->id;
      // $log->is_delete = $isdelete;
      $this->ledgerTransactions()->save($ledger_tran);
      return true;  

    }
}
