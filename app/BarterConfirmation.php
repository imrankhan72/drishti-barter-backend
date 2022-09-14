<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BarterConfirmation extends Model
{
    protected $fillable = ['barter_id','person_id','status','confirmation_type','confirmation_time'];

	protected $dates = ['created_at','updated_at'];

	public function barter(){
   		return $this->belongsTo('App\Barter','barter_id');
   	}

	public function person(){
   		return $this->belongsTo('App\Person','person_id');
   	}
}
