<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DisputeComment extends Model
{
    protected $fillable = ['barter_id','status','comment','dispute_id'];

    protected $dates = ['created_at','updated_at'];

    public function barter()
    {
    	return $this->belongsTo('App\Barter','barter_id');
    }
    public function dispute()
    {
    	return $this->belongsTo('App\Dispute','dispute_id');
    }
}
