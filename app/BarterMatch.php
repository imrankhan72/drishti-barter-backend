<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BarterMatch extends Model
{
    protected $fillable = ['barter_id','match_type','barter_2_id','person_id','total_lp_offered','local_inventory_type'];

	protected $dates = ['created_at','updated_at'];

	public function barter()
	{
		return $this->belongsTo('App\Barter','barter_id');
	}
	public function person()
	{
		return $this->belongsTo('App\Person','person_id');
	}
	public function barterMatchLocalInventoryLps()
	{
		return $this->hasOne('App\BarterMatchLocalInventoryLp','barter_match_id');
	}
	public function barterMatchLocalInventoryProducts()
    {
    return $this->hasOne('App\BarterMatchLocalInventoryProduct','barter_match_id');
    
    }
    public function barterMatchLocalInventoryServices()
    {
    return $this->hasOne('App\BarterMatchLocalInventoryService','barter_match_id');
    
    }
}
