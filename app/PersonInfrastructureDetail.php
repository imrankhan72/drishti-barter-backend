<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PersonInfrastructureDetail extends Model
{

	protected $table = 'person_intrastructure_details';
    protected $fillable = ['total_land_holding','irrigation_facilities','cultivable_land','crop_mapping','livestock','house_type','vehicles','own_house','storage_space','construction_material','machines','farming_equipment','person_id'];

    protected $dates = ['created_at','updated_at'];

    public function person()
    {
    	return $this->hasOne('App\Person','person_id');
    }
}
