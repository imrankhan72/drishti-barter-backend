<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BarterMatchLocalInventoryLp extends Model
{
    protected $fillable = ['barter_id','barter_match_id','lp'];

	protected $dates = ['created_at','updated_at'];

	public function barter()
	{
		return $this->belongsTo('App\Barter','barter_id');
	}
	public function barterMatch()
	{
		return $this->hasOne('App\BarterMatch','barter_match_id');
		
	}
}
