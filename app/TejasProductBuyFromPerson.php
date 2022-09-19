<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TejasProductBuyFromPerson extends Model
{
    protected $fillable = ['buy_request_id','product_id','quantity','unit','lp_applicable'];

    protected $dates = ['created_at','updated_at'];

    public function buy_request()
    {
    	return $this->belongsTo('App\TejasRequestBuyFromPeople','buy_request_id');
    }
    
    public function product()
    {
    	return $this->belongsTo('App\Product','product_id');
    }
}
