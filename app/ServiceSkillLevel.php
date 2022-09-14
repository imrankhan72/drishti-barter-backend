<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServiceSkillLevel extends Model
{
    protected $fillable =['skill_level','service_id'];

    protected $dates = ['created_at','updated_at'];

    public function service()
    {
    	return $this->belongsTo('App\Service','service_id');
    	
    }
}
