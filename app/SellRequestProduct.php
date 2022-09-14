<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SellRequestProduct extends Model
{
    protected $fillable = ['sell_request_id','product_id','quantity','unit','lp_applicable'];

    protected $dates = ['created_at','updated_at'];

    public function product()
    {
      return $this->belongsTo('App\Product','product_id');    
    }

    public function sellRequestProduct()
    {
      return $this->belongsTo('App\TejasProductSellRequest','sell_request_id');
    }
}
