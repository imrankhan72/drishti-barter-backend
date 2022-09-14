<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PersonSkill extends Model
{
     protected $fillable =['certification','duration'];
    protected $dates = ['created_at','updated_at'];

    public function person()
    {
    	return $this->belongsTo('App\Person','person_id');
    }
}
