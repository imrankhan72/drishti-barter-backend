<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
    protected $fillable = ['name','district_id','is_active'];

    protected $dates= ['created_at','updated_at'];

    public function district()
    {
    	return $this->belongsTo('App\District','district_id');
    }
    public function villages()
    {
    	return $this->hasMany('App\Village','block_id');
    }
}
