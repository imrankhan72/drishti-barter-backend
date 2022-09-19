<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PersonWorkExperience extends Model
{
     protected $fillable =['duration','title','person_id'];
      protected $dates = ['created_at','updated_at'];

      public function person()
      {
      	return $this->belongsTo('App\Person','person_id');
      }
}
