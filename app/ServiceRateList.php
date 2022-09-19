<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServiceRateList extends Model
{
    protected $fillable = ['state_id','highly_skilled','skilled','semi_skilled','onskilled','professionals','professionals_ratio','highly_skilled_ratio','skilled_ratio','semi_skilled_ratio','onskilled_ratio'];
    protected $dates = ['created_at','updated_at'];


    public function state()
    {
    	return $this->hasOne('App\State','state_id');
    }
}
