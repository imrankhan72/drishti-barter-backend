<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Geography extends Model
{
    use SoftDeletes;

    protected $fillable = ['name','type','parent_id','is_active','parent_pseudo_id','remote_id','state','district','block'];

    protected $dates= ['created_at','updated_at','deleted_at'];


    public function productAvailability()
    {
    	return $this->hasOne('App\ProductGeographyAvailability','product_id');
    	
    }
    public function personProduct()
    {
    	return $this->hasMany('App\PersonProduct','geography_id');
    }
    public function personService()
    {
        return $this->hasMany('App\PersonService','geography_id');
        
    }
    public function DmGeography()
    {
        return $this->hasMany('App\DMGeography','geography_id');
    }
    public function userGeographies()
    {
        return $this->hasMany('App\UserGeography','geography_id');
    }

    public function disputes()
    {
        return $this->hasMany('App\Dispute','geography_id');
        
    }
    public function barters()
    {
        return $this->hasMany('App\Barter','geography_id');
        
    }
    public static function getActiveGeography($data)
    {
        $geography = Geography::where('is_active',true)->whereDate('created_at',[$data['from_date'],$data['to_date']]);
        return $geography;
    }
    public function vaccinations()
    {
        return $this->hasMany('App\Vaccination','geography_id');
    }
    
}
