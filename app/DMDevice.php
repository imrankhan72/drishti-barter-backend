<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DMDevice extends Model
{
    protected $fillable = ['dm_id','device_properties'];

    protected $dates = ['created_at','updated_at'];

    public function dm()
    {
    	return $this->hasOne('App\DrishteeMitra','dm_id');
    }
}
