<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PersonTraining extends Model
{
     protected $fillable =['name','date_of_completion','person_id'];
      protected $dates = ['created_at','updated_at'];

      public function person()
      {
      	return $this->belongsTo('App\Person','person_id');
      }
}
