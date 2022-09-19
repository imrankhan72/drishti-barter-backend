<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductGeographyAvailability extends Model
{
    //
    protected $fillable = ['product_id','geography_id','geography_type','livelihood_points_override'];

    protected $dates = ['created_at','updated_at'];

    public function products()
    {
    	return $this->belongsTo('App\Product','product_id');

    }
    public function geography()
    {
    	return $this->belongsTo('App\Geography','geography_id');
    }
}
