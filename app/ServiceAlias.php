<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServiceAlias extends Model
{
    protected $fillable = ['service_id','service_translation','language'];

    protected $dates = ['created_at','updated_at'];

    public function service()
    {
    	return $this->belongsTo('App\Service','service_id');
    }
}
