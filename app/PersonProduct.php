<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PersonProduct extends Model
{
    protected $fillable = ['geography_id','geography_type','dm_id','person_id','product_id','unit_id','quantity_available','product_lp','active_on_barterplace','calc_margin_applicable','calc_hours_worked','calc_wage_applicable','calc_raw_material_cost'];

    protected $dates = ['created_at','updated_at'];

    public function geography()
    {
    	return $this->belongsTo('App\Geography','geography_id');
    }
    public function drishteeMitra()
    {
    	return $this->belongsTo('App\DrishteeMitra','dm_id');
    }
    public function person()
    {
    	return $this->belongsTo('App\Person','person_id');
    }
    public function product()
    {
    	return $this->belongsTo('App\Product','product_id');
    	
    }
    public function unit()
    {
    	return $this->belongsTo('App\Unit','unit_id');
    	
    }
    public function barterHaveProduct()
    {
        return $this->hasMany('App\PersonProduct','person_product_id');
        
    }
}
