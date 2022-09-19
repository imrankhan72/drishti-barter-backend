<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PersonLiveHoodEngagement extends Model
{
    protected $fillable = ['live_hood_engagements','description_of_le','group_activity_engagement','type','association','zone','person_id'];

    protected $dates = ['created_at','updated_at'];

    public function person()
    {
    	return $this->hasOne('App\Person','person_id');
    }
}
