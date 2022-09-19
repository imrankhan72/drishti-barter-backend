<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = ['name','short_name','conversion_factor','parent_unit_id','is_active'];

    protected $dates = ['created_at','updated_at'];

    public function products()
    {
    	return $this->hasMany('App\Product','unit_id');
    }
    public function personProducts()
    {
    	return $this->hasMany('App\PersonProduct','unit_id');
    	
    }
    public function userProducts()
    {
        return $this->hasMany('App\UserProduct','unit_id');
        
    }
}
