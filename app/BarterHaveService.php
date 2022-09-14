<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BarterHaveService extends Model
{
    protected $fillable = ['barter_id','person_service_id','no_of_days','service_lp'];

    protected $dates = ['created_at','updated_at'];

    public function barter()
    {
    	return $this->belongsTo('App\Barter','barter_id');
    }
    public function personService()
    {
    	return $this->belongsTo('App\PersonService','person_service_id');
    }
}
