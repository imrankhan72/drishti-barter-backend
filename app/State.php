<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // <-- This is required

class State extends Model
{
     use SoftDeletes;
    
    protected $fillable = ['name','country_id','is_active'];

    protected $dates= ['created_at','updated_at','deleted_at'];

    public function country()
    {
    	return $this->belongsTo('App\State','country_id');

    }
    public function districts()
    {
    	return $this->hasMany('App\District','state_id');
    }
    public function stateRateList()
    {
        return $this->hasOne('App\ServiceRateList','state_id');
        
    }
    public function drishteeMitras()
    {
        return $this->hasMany('App\DrishteeMitra','state_id');
    }
    public function person()
    {
        return $this->hasMany('App\Person','state_id');
        
    }
    
}
