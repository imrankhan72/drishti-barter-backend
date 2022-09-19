<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Vaccination extends Model
{
    protected $fillable = ['reg_id','added_by','person_id','geography_id','vaccine_name','dose_1_date','dose_2_date','dose_1_certificate_name','dose_1_certificate_path','dose_2_certificate_name','dose_2_certificate_path','dose_1_complete','dose_2_complete','gender','dose_1_place','dose_2_place','is_dose_1','is_dose_2','certificate_dose_1_upload_date','certificate_dose_2_upload_date'];

    protected $dates = ['created_at','updated_at'];

    public function addedBy()
    {
    	return $this->belongsTo('App\DrishteeMitra','added_by');
    }
    public function person()
    {
    	return $this->belongsTo('App\Person','person_id');
    }
    public function geography()
    {
    	return $this->belongsTo('App\Geography','geography_id');
    }
}
