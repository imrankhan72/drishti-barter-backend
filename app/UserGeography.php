<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserGeography extends Model
{
    protected $fillable = ['user_id','geography_id','geography_type'];

    protected $dates = ['created_at','updated_at'];

    public function user()
    {
    	return $this->belongsTo('App\User','user_id');
    }
    public function geography()
    {
    	return $this->belongsTo('App\Geography','geography_id');
    }

    public function dispute(){
        return $this->hasMany('App\Dispute','geography_id','geography_id');
    }
}
