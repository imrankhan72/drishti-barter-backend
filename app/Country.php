<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // <-- This is required

class Country extends Model
{
     use SoftDeletes;
    
    protected $fillable = ['name','is_active'];

    protected $dates= ['created_at','updated_at','deleted_at'];

    public function states()
    {
    	return $this->hasMany('App\State','country_id');
    }
}
