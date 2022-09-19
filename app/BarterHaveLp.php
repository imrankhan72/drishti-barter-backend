<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BarterHaveLp extends Model
{
    protected $fillable = ['barter_id','lp'];

    protected $dates = ['created_at','updated_at'];

    public function barter()
    {
    	return $this->belongsTo('App\Barter','barter_id');
    }
}
