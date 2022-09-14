<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // <-- This is required

class ProductCategory extends Model
{
    //

    use SoftDeletes;

    protected $fillable = ['name','icon_name','icon_path','is_active'];

    protected $dates = ['created_at','updated_at','deleted_at'];

    public function products()
    {
    	return $this->hasMany('App\Product','product_category_id');
    	
    }
}
