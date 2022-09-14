<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SellRequestComment extends Model
{
    protected $fillable = ['sell_request_id','commentor_id','comment','date_time'];

    protected $dates = ['created_at','updated_at'];


    public function tejasProductSellRequests()
    {
    	return $this->belongsTo('App\TejasProductSellRequest','sell_request_id');
    }
    public function commentor()
    {
    	return $this->belongsTo('App\User','commentor_id');
    	
    }
}
