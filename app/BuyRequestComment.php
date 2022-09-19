<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BuyRequestComment extends Model
{
    protected $fillable = ['buy_request_id','commentor_id','comment','date_time'];

    protected $dates= ['created_at','updated_at'];

    public function tejasProductBuyRequests()
    {
    	return $this->belongsTo('App\TejasProductBuyRequest','buy_request_id');
    }
    public function commentor()
    {
    	return $this->belongsTo('App\User','commentor_id');
    	
    }
}
