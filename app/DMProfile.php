<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DMProfile extends Model
{
    protected $fillable = ['dob','address','photo_name','photo_path','whatsapp_no','alternative_mobile_no','dm_id'];

    protected $dates = ['created_at','updated_at'];

    public function drishteeMitra()
    {
    	return $this->hasOne('App\DrishteeMitra','dm_id');
    }
}
