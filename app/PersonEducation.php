<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PersonEducation extends Model
{
    protected $fillable = ['digital_literacy','max_qualification','person_id'];
    protected $dates = ['created_at','updated_at'];

    public function person()
    {
    	return $this->hasOne('App\Person','person_id');
    }
}
