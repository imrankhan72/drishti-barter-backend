<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserProduct extends Model
{
    protected $fillable = ['user_id','product_id','unit_id','quantity_available','product_lp','tag'];

    protected $dates = ['created_at','updated_at'];

    
    public function user()
    {
    	return $this->belongsTo('App\User','user_id');
    }
    public function product()
    {
    	return $this->belongsTo('App\Product','product_id');
    	
    }
    public function unit()
    {
    	return $this->belongsTo('App\Unit','unit_id');
    	
    }
    public function userProductLog()
    {
        return $this->hasMany('App\UserProductLog','user_product_id');
    }
    public static function transformData($userproducts)
    {
        $res = collect();
        foreach ($userproducts as $up) {
            $temp['Product Name'] = $up->product->name;
            $temp['Quantity Available'] = $up->quantity_available;
            $temp['Product Lp'] = $up->product_lp;
            $temp['Product unit'] = $up->product->units->name;
            $temp['Product Added Date'] = Carbon::parse($up->created_at)->format('Y/m/d');
            $temp['User Name'] = $up->user->first_name.' '.$up->user->last_name;
            $userGeographies = UserGeography::where('user_id',$up->user_id)->first();
            $geography = Geography::find($userGeographies->geography_id);
            $temp['Geography Name'] = $geography->name;
            $temp['State'] = $geography->state;
            $temp['District'] = $geography->district;
            $temp['Available Lp'] = $up->user->ledger->balance; 
            $res->push($temp);
        }
        return $res;
    }
    
}
