<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductAlias extends Model
{
    protected $fillable = ['product_id','product_translation','language'];


    protected $dates = ['created_at','updated_at'];

    public function product()
    {
    	return $this->belongsTo('App\Product','product_id');
    }
}
