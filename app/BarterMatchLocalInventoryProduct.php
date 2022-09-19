<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BarterMatchLocalInventoryProduct extends Model
{
    protected $fillable = ['barter_id','barter_match_id','product_id','product_quantity','product_lp'];

	protected $dates = ['created_at','updated_at'];

	public function barter()
	{
		return $this->belongsTo('App\Barter','barter_id');
	}
	public function barterMatch()
	{
		return $this->belongsTo('App\BarterMatch','barter_match_id');
		
	}
	public function product()
	{
		return $this->belongsTo('App\Product','product_id');
	}
}
