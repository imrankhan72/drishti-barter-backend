<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ledger extends Model
{
    protected $fillable = ['balance','ledger_id','ledger_type'];

    protected $dates= ['created_at','updated_at'];

    public function ledger()
    {
    	return $this->morphTo();
    }
    public function person()
    {
    	return $this->hasOne('App\Person','ledger_id');
    }
    public function user()
    {
        return $this->hasOne('App\User','ledger_id');
        
    }
    public function drishteeMitra()
    {
        return $this->hasOne('App\DrishteeMitra','ledger_id');
        
    }
    public function ladgerTransaction(){
        return $this->hasMany('App\LedgerTransaction','ledger_id');
    }
}
