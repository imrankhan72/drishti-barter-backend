<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\LedgerTransaction;

class TejasProductSellRequest extends Model
{
    protected $fillable = ['requester_id','request_date','status','approved_by'];

    protected $dates = ['created_at','updated_at'];


    public function dm()
    {
    	return $this->belongsTo('App\DrishteeMitra','requester_id');
    }

    public function sellRequestProducts()
    {
      return $this->hasMany('App\SellRequestProduct','sell_request_id');
    }
    public function sellRequestComments()
    {
      return $this->hasMany('App\SellRequestComment','sell_request_id');
    }
    public function approvedByAdmin()
    {
      return $this->belongsTo('App\User','approved_by');
    }
    public function ledgerTransactions()
    {
        return $this->morphMany(LedgerTransaction::class, 'ledgerTransaction');
    }


    /**
     *
     * @param  $type Success/Fail
     * @param  $ledger_id of DM
     * @param  $transaction_type of transaction credit/debit
     * @param  $amount transaction amount
     * @param  $note comment
     * @param  $balance_after_transaction after transaction remainig balance
     * @return true
     * do store DM Ledger transaction
     */
    public function createTejasSellLedgerTransactions($type,$ledger_id,$transaction_type,$amount,$note,$balance_after_transaction,$person_id=null)
    {
     $ledger_tran = new LedgerTransaction();
      // $ledger->ledger_id = $id;
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
