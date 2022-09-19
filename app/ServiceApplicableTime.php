<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServiceApplicableTime extends Model
{
    protected $fillable = ['applicable_time','service_id'];

    protected $dates = ['created_at','updated_at'];

    public function service()
    {
    	return $this->belongsTo('App\Service','service_id');
    }
}
