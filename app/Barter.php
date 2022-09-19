<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Ledger;
use App\LedgerTransaction;

class Barter extends Model
{
   protected $fillable = ['person_id','added_by_dm_id','geography_id','geography_type','is_barter_expire','barter_expire_date','status','barter_total_lp_offered','barter_total_lp_needed','is_disputed','barter_date_time_added'];

   protected $dates = ['created_at','updated_at'];

   public function drisheeMitras()
   {
     return $this->belongsTo('App\DrishteeMitra','added_by_dm_id');
   }
   public function geography()
   {
     return $this->belongsTo('App\Geography','geography_id');
   }
   public function person()
   {
   	return $this->belongsTo('App\Person','person_id');
   }
   public function barterHaveServices()
   {
   	return $this->hasMany('App\BarterHaveService','barter_id');
   }
   public function barterHaveProducts()
   {
   	return $this->hasMany('App\BarterHaveProduct','barter_id');
   	
   }
   public function barterHaveLp()
   {
   	return $this->hasMany('App\BarterHaveLp','barter_id');
   	
   }
   public function barterNeedLp()
   {
      return $this->hasMany('App\BarterNeedLp','barter_id');
      
   }
   public function barterNeedServices()
   {
      return $this->hasMany('App\BarterNeedService','barter_id');
      
   }
   public function barterNeedProducts()
   {
      return $this->hasMany('App\BarterNeedProduct','barter_id');
      
   }
   public function barterConfirmation()
   {
      return $this->hasMany('App\BarterConfirmation','barter_id');
      
   }
   public function barterMatches()
   {
      return $this->hasMany('App\BarterMatch','barter_id');
      
   }
   public function barterMatchLocalInventoryLps()
   {
      return $this->hasMany('App\BarterMatchLocalInventoryLp','barter_id');
    
   }
   public function barterMatchLocalInventoryProducts()
    {
    return $this->hasMany('App\BarterMatchLocalInventoryProduct','barter_id');
    
    }
    public function barterMatchLocalInventoryServices()
    {
    return $this->hasMany('App\BarterMatchLocalInventoryService','barter_id');
    
    }
    public function dispute()
    {
      return $this->hasMany('App\Dispute','barter_id');
    }
    public function comments()
    {
      return $this->hasMany('App\Dispute','barter_id');
      
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
     * do store Bater transaction details
     */
    public function createBarterLedgerTransactions($type,$ledger_id,$transaction_type,$amount,$note,$balance_after_transaction,$person_id){
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
