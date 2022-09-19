<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\LedgerTransaction;

class TejasRequestBuyFromPeople extends Model
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

    public function buyFromPersonProducts(){
        return $this->hasMany('App\TejasProductBuyFromPerson','buy_request_id');
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
    public function createTejasBuyLedgerTransactions($type,$ledger_id,$transaction_type,$amount,$note,$balance_after_transaction,$person_id=null)
    {
     $ledger_tran = new LedgerTransaction();
      $ledger_tran->person_id = $person_id;
      $ledger_tran->ledger_id = $ledger_id;
      $ledger_tran->transaction_type = $transaction_type;
      $ledger_tran->amount = $amount;
      $ledger_tran->transaction_note = $note;
      $ledger_tran->ledgerTransaction_type = $type;
      $ledger_tran->balance_after_transaction = $balance_after_transaction;
      $ledger_tran->person_id = $person_id;
      // $log->user_id = $user->id;
      // $log->is_delete = $isdelete;
      $this->ledgerTransactions()->save($ledger_tran);
      return true;  

    }
}
