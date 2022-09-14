<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BarterNeedService extends Model
{
    protected $fillable = ['barter_id','service_id','no_of_days','skill_required','service_lp'];

    protected $dates = ['created_at','updated_at'];

    public function barter()
    {
    	return $this->belongsTo('App\Barter','barter_id');
    }
    public function service()
    {
    	return $this->belongsTo('App\Service','service_id');
    }
}
