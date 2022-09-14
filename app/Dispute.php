<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dispute extends Model
{
    protected $fillable = ['barter_id','name','added_by','date_added','status','geography_id'];

    protected $dates = ['created_at','updated_at'];

    public function barter()
    {
    	return $this->belongsTo('App\Barter','barter_id');
    }
    public function addedBy()
    {
    	return $this->belongsTo('App\DrishteeMitra','added_by');
    	
    }
    public function comments()
    {
    	return $this->hasMany('App\DisputeComment','dispute_id');
    }
    public function geography()
    {
        return $this->belongsTo('App\Geography','geography_id');
    }
}
