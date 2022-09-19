<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\LedgerTransaction;

class LpRequestFromAdmin extends Model
{
    protected $fillable = ['admin_id','superadmin_approver_id','points_needed','status'];

    protected $dates = ['created_at','updated_at'];

    public function ledgers()
    {
        return $this->morphMany(Ledger::class, 'ledger')->orderBy('id','DESC');
    }
    public function ledgerTransactions()
    {
        return $this->morphMany(LedgerTransaction::class, 'ledgerTransaction')->orderBy('id','DESC');
    	
    }
    public function requestedByAdmin()
    {
    	return $this->belongsTo('App\User','admin_id');
    }
    public function approvedBySuperAdmin()
    {
    	return $this->belongsTo('App\User','superadmin_approver_id');
    	
    }
    // public function ledgerTransactions()
    // {
    //     return $this->morphMany(LedgerTransaction::class, 'ledgerTransaction')->orderBy('id','DESC');
    // }

    /**
     *
     * @param  $type Success/Fail
     * @param  $ledger_id of Person
     * @param  $transaction_type of transaction credit/debit
     * @param  $amount transaction amount
     * @param  $note comment
     * @param  $balance_after_transaction after transaction remainig balance
     * @return true
     * do store Admin transaction details
     */
    public function createLpRequestLedgerTransaction($type,$ledger_id,$transaction_type,$amount,$note,$balance_after_transaction){
     $ledger_tran = new LedgerTransaction();
      // $ledger->ledger_id = $id;
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
