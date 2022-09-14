<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // <-- This is required

class District extends Model
{
     use SoftDeletes;
    
   protected $fillable = ['name','state_id','is_active'];

    protected $dates= ['created_at','updated_at','deleted_at'];

    public function city()
    {
    	return $this->hasMany('App\City','district_id');
    }
    public function blocks()
    {
    	return $this->hasMany('App\Block','district_id');
    }
    public function states()
    {
        return $this->belongsTo('App\State','state_id');
    }
}
