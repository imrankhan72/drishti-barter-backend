<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PersonService extends Model
{
    protected $fillable = ['geography_id','geography_type','dm_id','person_id','service_id','service_lp','active_on_barterplace','skill_level'];

    protected $dates = ['created_at','updated_at'];

    public function geography()
    {
    	return $this->belongsTo('App\Geography','geography_id');
    }
    public function drishteeMitra()
    {
    	return $this->belongsTo('App\DrishteeMitra','dm_id');
    }
    public function person()
    {
    	return $this->belongsTo('App\Person','person_id');
    }
    public function service()
    {
        return $this->belongsTo('App\Service','service_id');
        
    }
    public function barterHaveService()
    {
        return $this->hasMany('App\BarterHaveService','person_service_id');
    }
    
}
