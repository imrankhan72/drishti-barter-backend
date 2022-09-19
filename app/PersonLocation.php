<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PersonLocation extends Model
{
            
    protected $fillable = ['state','city','block','village','pincode','latitude','longitude','person_id','area_type'];
    protected $dates = ['created_at','updated_at'];

    public function person()
    {
    	return $this->hasOne('App\Person','person_id');
    }
}
