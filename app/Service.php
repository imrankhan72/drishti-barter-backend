<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;
class Service extends Model
{
    use SoftDeletes;
     protected $fillable = ['name','default_livelihood_points','service_category_id','added_by_user_id','availability','approved_by','is_approved','icon_name','icon_path'];

     protected $dates = ['created_at','updated_at','deleted_at'];

     public function serviceAlias()
     {
     	return $this->hasMany('App\ServiceAlias','service_id');
     }
     public function serviceCategory()
     {
     	return $this->belongsTo('App\ServiceCategory','service_category_id');
     }
     public function addedBy()
     {
     	return $this->belongsTo('App\DrishteeMitra','added_by_user_id');
     }
     public function approvedBy()
     {
     	return $this->belongsTo('App\User','approved_by');
     	
     }
     public function skillLevel()
     {
         return $this->hasMany('App\ServiceSkillLevel','service_id');
     }
     public function applicableTime()
     {
         return $this->hasMany('App\ServiceApplicableTime','service_id');
         
     }
     public function personService()
     {
         return $this->hasMany('App\PersonService','service_id');
         
     }
     public function barterNeedService()
     {
         return $this->hasMany('App\BarterNeedService','service_id');
         
     }
     public function barterMatchLocalInventoryServices()
    {
    return $this->hasMany('App\BarterMatchLocalInventoryService','service_id');
    
    }

    /**
     *
     * @param  \Illuminate\Http\Request $request
     * @return \App\Service $service
     * do filter service depend upon name, service_category, created_at
     */
    public static function filterServices($request){
      $services = DB::table('services');
      if(isset($request['filters'])) {
        $filters = $request['filters'];
        foreach($filters as $key => $value) {
         
          if($key == 'name'){
                        $services = $services->where($key,'like','%'.$value.'%');
                    
           }
           
          else if($key == 'service_category_id' || $key == 'added_by_user_id') {
                    if(in_array('--', $value)) {
                        $services = $services->whereNull($key);   
                    }
                    else {
                        $services = $services->whereIn($key, $value);
                    }
                }
          else if($key == 'created_at') {
            $start_date = isset($value['start_date']) ? $value['start_date'] : null;
            $end_date = isset($value['end_date']) ? $value['end_date'] : null;
                    if($start_date && $end_date) {
                        $services = $services->whereBetween($key, array($start_date, $end_date));
                    }
          }
        
        }
      }
        
        if(isset($request['filters']['sortBy']) && !empty($request['filters']['sortBy'])) {
            $sortBy = $request['filters']['sortBy'];
            foreach($sortBy as $key => $value) {
                if($key == 'name') {
                    $services->orderBy('name',$value);
                }
                else {
                    $services->orderBy($key, $value);
                }
            }
        }
        else {
            $services->orderBy('id', 'DESC');
        }
    
        if(isset($request['count']) && $request['count']) {
            $services = $services->get();
            return count($services);
        }
        $offset = isset($request['skip']) ? $request['skip'] : 0 ;
        $chunk = isset($request['skip']) ? $request['limit'] : 999999;
        $services = $services->skip($offset)->limit($chunk)->get();
        $servicesCollection = collect();
        foreach($services as $service) {
            $c = Service::find($service->id);
            $c->load('serviceAlias','approvedBy','addedBy','serviceCategory','skillLevel','applicableTime');
            $servicesCollection->push($c);
        }
      return $servicesCollection;
    }
}
