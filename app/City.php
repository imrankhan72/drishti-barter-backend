<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // <-- This is required


class City extends Model
{
     use SoftDeletes;

    protected $fillable = ['name','district_id','is_active'];

    protected $dates= ['created_at','updated_at','deleted_at'];

    public function districts()
    {
    	return $this->belongsTo('App\District','district_id');
    }
   
}
