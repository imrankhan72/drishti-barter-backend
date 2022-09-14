<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // <-- This is required

class ServiceCategory extends Model
{
	use SoftDeletes;
    protected $fillable = ['name','is_active','icon_path','icon_name'];
    protected $dates = ['created_at','updated_at','deleted_at'];


    public function services()
    {
    	return $this->hasMany('App\Service','service_category_id');
    }
}
