<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserProductLog extends Model
{
    protected $fillable = ['mr_no','product_id','user_product_id','quantity','product_lp','message','amount'];

    protected $dates = ['created_at','updated'];

    public function product()
    {
    	return $this->belongsTo('App\Product','product_id');
    }
    public function userProduct()
    {
    	return $this->belongsTo('App\UserProduct','user_product_id');
    }
}
