<?php
//updated on 2nd nov
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // <-- This is required
use DB;
use App\ProductGeographyAvailability;
class Product extends Model
{
	use SoftDeletes;

  protected $fillable = ['name','default_livehood_points','product_category_id','calc_raw_material_cost','calc_hours_worked','calc_wage_applicable','calc_margin_applicable','added_by_user_id','is_gold_product','is_branded_product','mrp','availability','approved_by','is_approved','photo_path','photo_name','approved_at','unit_id'];
  protected $dates = ['created_at','updated_at','deleted_at'];
  
  public function geoDefaultLivehoodPoints($geo_id)
  {
      $pga = ProductGeographyAvailability::where("product_id",$this->id)->where("geography_id",$geo_id)->first();
      if($pga){
        return $pga->livelihood_points_override;
      }else{
        return false;
      }
  }
  public function productCategory()
  {
  	return $this->belongsTo('App\ProductCategory','product_category_id');
  }
  public function approvedBy()
  {
  	return $this->belongsTo('App\User','approved_by');
  }
  public function units()
  {
  	return $this->belongsTo('App\Unit','unit_id');
  }
  public function productAlias()
  {
    return $this->hasMany('App\ProductAlias','product_id');
  }
  public function geographyProduct()
  {
    return $this->hasMany('App\ProductGeographyAvailability','product_id');
  }
  public function userAdded()
  {
    return $this->belongsTo('App\DrishteeMitra','added_by_user_id');
  }
  public function personProduct()
  {
    return $this->hasMany('App\PersonProduct','product_id');
    
  }
  public function userProduct()
  {
    return $this->hasMany('App\UserProduct','product_id');
    
  }
  public function barterNeedProduct()
  {
    return $this->hasMany('App\BarterNeedProduct','product_id');
  }
  public function barterMatchLocalInventoryProducts()
  {
    return $this->hasMany('App\BarterMatchLocalInventoryProduct','product_id');
    
  }
  public function sellRequestProducts()
  {
    return $this->hasMany('App\SellRequestProduct','product_id');
    
  }
  public function userProductLog()
  {
    return $this->hasMany('App\UserProductLog','product_id');
  }
  /**
   *
   * @param  \Illuminate\Http\Request $request
   * @return \App\Product $product
   * do filter Product depend upon name, product_category, created_at
   */
public static function filterProducts($request)
    {
        if(isset($request['skip']) && $request['limit']) {
            $products = Product::where('deleted_at','=',null)->skip($request['skip'])->take($request['limit'])->with('productCategory')->with('personProduct')
                ->with('approvedBy')->with('units')->with('productAlias')->with('userAdded')->with('geographyProduct')->orderBy('name')->get();
        }else{
            $products = Product::where('deleted_at','=',null)->with('productCategory')->with('personProduct')
                ->with('approvedBy')->with('units')->with('productAlias')->with('userAdded')->with('geographyProduct')->orderBy('name')->get();
        }

      if(isset($request['filters'])) {
        $filters = $request['filters'];
        foreach($filters as $key => $value) {

          if($key == 'name'){
                        $products = $products->where($key,'like','%'.$value.'%');

           }
           else if($key == 'is_gold_product'){
                        $products = $products->where($key,$value);

           }
          else if($key == 'product_category_id' || $key == 'added_by_user_id' || $key == 'unit_id' || $key == 'salutation_id') {
                    if(in_array('--', $value)) {
                        $products = $products->whereNull($key);
                    }
                    else {
                        $products = $products->whereIn($key, $value);
                    }
                }
          else if($key == 'created_at') {
            $start_date = isset($value['start_date']) ? $value['start_date'] : null;
            $end_date = isset($value['end_date']) ? $value['end_date'] : null;
                    if($start_date && $end_date) {
                        $products = $products->whereBetween($key, array($start_date, $end_date));
                    }
          }

        }
      }

        if(isset($request['count']) && $request['count']) {
            $products = $products->get();
            return count($products);
        }

       return $products;

        $productsCollection = collect();

//        foreach ($products as $p) {
//          $cd = Product::find($p->id);
//          if($request['geo_id'] && $cd->is_gold_product){
//            $lp = $cd->geoDefaultLivehoodPoints($request['geo_id']);
//            if($lp){
//              $cd->default_livehood_points = $lp;
//            }
//          }
//          if($cd){
//            $cd->load('productCategory','personProduct','approvedBy','units','productAlias','userAdded','geographyProduct');
//           // if(!$p->is_gold_product){
//            $productsCollection->push($cd);
//           // }
//          }
//        }
      return $productsCollection;
    }
    public static function getTejasProduct($data)
    {
      $product = Product::where('is_gold_product',true)->whereDate('created_at',[$data['from_date'],$data['to_date']]);
        return $product;
    }
    public static function getProduct($data)
    {
     $product = Product::where('is_gold_product',true)->whereDate('created_at',[$data['from_date'],$data['to_date']]);
        return $product; 
    }
}
