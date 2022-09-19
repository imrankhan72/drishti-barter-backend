<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BarterHaveProduct extends Model
{
    protected $fillable = ['barter_id','person_product_id','quantity','product_lp','quantity_unit'];

    protected $dates = ['created_at','updated_at'];

    public function barter()
    {
    	return $this->belongsTo('App\Barter','barter_id');
    }
    public function personProduct()
    {
    	return $this->belongsTo('App\PersonProduct','person_product_id');
    	
    }
}
