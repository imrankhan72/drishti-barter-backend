<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PersonBankAccountDetail extends Model
{
     protected $fillable =['account_number','bank_name','ifsc_code','payee_name','person_id'];
     protected $dates = ['created_at','updated_at'];

     public function person()
     {
     	return $this->hasOne('App\Person','person_id');
     }
}
