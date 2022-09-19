<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PersonIncome extends Model
{
    protected $fillable =['monthly_income','bpl_card_holder','income_type','person_id'];
    protected $dates = ['created_at','updated_at'];

    public function person()
    {
    	return $this->hasOne('App\Person','person_id');
    }
}
