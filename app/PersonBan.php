<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PersonBan extends Model
{
    protected $fillable =['requester_id','person_id','approver_id','status','comment'];
    protected $dates = ['created_at','updated_at'];

    public function person()
    {
    	return $this->belongsTo('App\Person','person_id');
    }
    public function user()
    {
        return $this->belongsTo('App\User','approver_id');
    }
    public function dm()
    {
        return $this->belongsTo('App\DrishteeMitra','requester_id');
    }
}
