<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // <-- This is required


class Village extends Model
{
    use SoftDeletes;
	
    protected $fillable = ['name','block_id','is_active'];

    protected $dates= ['created_at','updated_at','deleted_at'];

    public function blocks()
    {
    	return $this->belongsTo('App\Block','block_id');
    }
}
