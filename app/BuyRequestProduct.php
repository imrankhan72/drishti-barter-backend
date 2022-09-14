<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BuyRequestProduct extends Model
{
    protected $fillable = ['buy_request_id','product_id','quantity','unit','lp_applicable'];

    protected $dates= ['created_at','updated_at'];

    public function product()
    {
      return $this->belongsTo('App\Product','product_id');    
    }

    public function BuyRequestProduct()
    {
      return $this->belongsTo('App\TejasProductBuyRequest','buy_request_id');
    }

}
