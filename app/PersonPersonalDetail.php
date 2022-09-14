<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PersonPersonalDetail extends Model
{
    protected $fillable = ['dob','marital_status','gender','disability','religion','caste','language','person_id','photo_name','photo_path'];

    protected $dates = ['created_at','updated_at'];

    public function person()
    {
    	return $this->hasOne('App\Person','person_id');
    }
}
