<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BarterMatchLocalInventoryService extends Model
{
    protected $fillable = ['barter_id','barter_match_id','service_id','no_of_days','service_lp'];

	protected $dates = ['created_at','updated_at'];
	public function barter()
	{
		return $this->belongsTo('App\Barter','barter_id');
	}
	public function barterMatch()
	{
		return $this->belongsTo('App\BarterMatch','barter_match_id');
		
	}
	public function service()
	{
		return $this->belongsTo('App\Service','service_id');
	}
}
