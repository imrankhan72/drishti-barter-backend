<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TejasProductSellToPerson extends Model
{
    protected $fillable = ['sell_request_id','product_id','quantity','unit','lp_applicable'];

    protected $dates = ['created_at','updated_at'];

    public function sell_request()
    {
        return $this->belongsTo('App\TejasRequestSellToPeople','sell_request_id');
    }
    
    public function product()
    {
        return $this->belongsTo('App\Product','product_id');
    }
}
