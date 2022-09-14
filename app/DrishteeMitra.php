<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Hash;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\DrishteeMitra;
use Illuminate\Database\Eloquent\SoftDeletes;

class DrishteeMitra extends Authenticatable implements JWTSubject
{
  use SoftDeletes;
    protected $fillable = ['first_name','middle_name','password','last_name','email','mobile','last_password_change','ledger_id','otp','user_type','is_mobile_onboarded','status','person_id','added_by','added_on','state_id','device_token','remote_id'];

    protected $dates = ['created_at','updated_at','deleted_at'];

    protected $hidden = [
        'password'
    ];

    protected $table = 'drishtree_mitras';

    /**
     *
     * @param  $mobile 
     * @param  $otp
     * @return \App\DrishteeMitra $dm
     * do match dm otp 
     */
    public static function authenticateUser($mobile, $otp){
        $dm = DrishteeMitra::where('mobile', '=', $mobile)->first();
        if($dm){
            if ($dm->otp == $otp) {
                return $dm;
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

    public function dmProfile()
    {
        return $this->hasOne('App\DMProfile','dm_id');
    }
    public function dmDevice()
    {
        return $this->hasOne('App\DMDevice','dm_id');

    }
    public function services()
    {
        return $this->hasMany('App\Service','added_by_user_id');
        
    }
    public function productAdded()
    {
        return $this->hasMany('App\Product','added_by_user_id');
    }
    public function personProduct()
    {
        return $this->hasMany('App\PersonProduct','dm_id');
        
    }
    public function addedBarters()
    {
        return $this->hasMany('App\Barter','added_by_dm_id');
        
    }

    public function dmGeography()
    {
        return $this->hasOne('App\DMGeography','dm_id');
    }
    public function state()
    {
        return $this->belongsTo('App\State','state_id');
    }
    public function ledgers()
    {
        return $this->morphMany(Ledger::class, 'ledger')->orderBy('id','DESC');
    }
    public function ledger()
    {
        return $this->belongsTo('App\Ledger','ledger_id');
      
    }

    /**
     *
     * @param  $type Success/Fail
     * @param  $balance transaction 
     * @return true
     * do store dm transaction details
     */
    public function createDmTransaction($type,$balance){
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
    public function tejasProductSellRequests()
    {
      return $this->hasMany('App\TejasProductSellRequest','requester_id');    
    }
    public function tejasProductBuyRequests()
    {
      return $this->hasMany('App\TejasProductBuyRequest','requester_id');    
    }
    public function dispute()
    {
      return $this->hasMany('App\Dispute','added_by');     
    }

    public function person(){
        return $this->belongsTo('App\Person','person_id');
    }
    public function personAddedBy()
    {
        return $this->hasMany('App\Person','dm_id');
    }
    public function personBans()
    {
        return $this->hasMany('App\PersonBan','requester_id');
        
    }
    public static function getActiveMitra($data)
    {
     $dm = DrishteeMitra::where('status',true)->whereDate('created_at',[$data['from_date'],$data['to_date']]);
        return $dm; 
    }
}
