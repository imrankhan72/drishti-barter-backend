<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BarterNeedProduct extends Model
{
    protected $fillable= ['barter_id','product_id','quantity','product_lp'];

    protected $dates = ['created_at','updated_at'];

    public function barter()
    {
    	return $this->belongsTo('App\Barter','barter_id');
    }
    public function product()
    {
    	return $this->belongsTo('App\Product','product_id');
    }
}
