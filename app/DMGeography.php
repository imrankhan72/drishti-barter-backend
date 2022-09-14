<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DMGeography extends Model
{
    protected $fillable = ['dm_id','geography_id','added_by','added_on'];

    protected $dates = ['created_at','updated_at'];

    public function drihsteeMitra()
    {
    	return $this->hasOne('App\DrishteeMitra','dm_id');
    }
    public function geography()
    {
    	return $this->belongsTo('App\Geography','geography_id');
    }
    public function addedBy()
    {
    	return $this->belongsTo('App\User','added_by');
    }
}
