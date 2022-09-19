<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Hash;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Ledger;
use App\LedgerTransaction;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name','last_name','email', 'mobile','password','is_super_admin', 'last_password_change' ,'ledger_id','status','is_management','remote_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password'
    ];

    protected $dates = ['created_at','updated_at','deleted_at'];

    /**
     *
     * @param  $email 
     * @param  $password
     * @return \App\User $user
     * do match user password
     */
    public static function authenticateUser($email, $password){
        $user = User::where('email', '=', $email)->first();
        if($user){
            if (Hash::check($password, $user->password)) {
                return $user;
            } else {
                throw new \App\Repositories\Exceptions\AuthenticationException;
            }
        }
        else{
            throw new \App\Repositories\Exceptions\ModelNotFound;
        }
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
 
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */

    
    
    public function getJWTCustomClaims()
    {
        return [];
    }
    public function productApproved()
    {
        return $this->hasMany('App\Product','approved_by');
    }
    
    public function serviceApproved()
    {
        return $this->hasMany('App\Service','approved_by');
        
    }
    public function DmGeography()
    {
        return $this->hasMany('App\DMGeography','added_by'); // DM Geography 
    }
    public function ledgers()
    {
        return $this->morphMany(Ledger::class, 'ledger')->orderBy('id','DESC');
    }
    public function personBans()
    {
        return $this->hasMany('App\PersonBan','approver_id'); // Ban Person 
      
    }
    public function userProducts()
    {
        return $this->hasMany('App\UserProduct','user_id');
        
    }
    public function tejasProductSellRequestApprovedBy()
    {
        return $this->hasMany('App\TejasProductSellRequest','approved_by'); 
    }
    public function ledger(){
        return $this->belongsTo('App\Ledger', 'ledger_id');
    }
    public function drishteeMitra()
    {
        return $this->hasMany('App\DrishteeMitra','added_by');
    }

    /**
     *
     * @param  $type Success/Fail
     * @param  $balance transaction 
     * @return true
     * do store user transaction details
     */
    public function createUserTransaction($type,$balance){
    // $user = Auth::User();
      $ledger = new Ledger();
      // $ledger->ledger_id = $id;
      $ledger->ledger_type = $type;
      $ledger->balance = $balance;
      // $log->user_id = $user->id;
      // $log->is_delete = $isdelete;
      $this->ledgers()->save($ledger);
      return true;   
    }

    public function ledgerTransactions()
    {
        // return LedgerTransaction::where("ledger_id",$this->ledger_id)->get();
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
     * do store user Ledger transaction
     */
    public function createUserLedgerTransactions($type,$ledger_id,$transaction_type,$amount,$note,$balance_after_transaction){
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
    public function lpRequestFromAdmin()
    {
        return $this->hasMany('App\LpRequestFromAdmin','admin_id');
    }
    public function approvedBySuperAdmin()
    {
        return $this->hasMany('App\LpRequestFromAdmin','superadmin_approver_id');
        
    }
    public function sellRequestComments()
    {
        return $this->hasMany('App\SellRequestComment','commentor_id');
    }
    public function buyRequestComments()
    {
        return $this->hasMany('App\BuyRequestComment','commentor_id');
    }
    public function userGeographies()
    {
      return $this->hasMany('App\UserGeography','user_id');
    }
}
