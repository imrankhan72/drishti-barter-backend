<?php

namespace App\Http\Controllers;

use App\PersonProduct;
use Illuminate\Http\Request;
use App\Repositories\Repository\PersonProductRepository;
use Validator;
use App\Product;
use App\Person;
use App\TejasProductSellRequest;
use App\DrishteeMitra;
use App\TejasProductSellToPerson;
use App\TejasProductBuyFromPerson;
use App\MarginPercentage;

class PersonProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private $repository;
    public function __construct(PersonProductRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index()
    {
     return Response()->json($this->repository->all()->load('geography','unit','product','drishteeMitra','person'),200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $validation = Validator::make($request->all(),[
          'geography_id'            => 'required|exists:geographies,id',
          'geography_type'          => 'required' ,
          'dm_id'                   => 'required|exists:drishtree_mitras,id',
          'person_id'               => 'required|exists:people,id',
          'product_id'              => 'required|exists:products,id',
          'unit_id'                 => 'required|exists:units,id',
          'quantity_available'      => 'required',
          'product_lp'              => 'required',
          'active_on_barterplace'   => 'required|boolean',
          'calc_raw_material_cost'  => 'required|numeric',
          'calc_wage_applicable'    => 'required|numeric',
          'calc_hours_worked'       => 'required|numeric',
          'calc_margin_applicable'  => 'required|numeric'

      ]);
      if($validation->fails()) {
        $errors =$validation->errors();
        return response()->json($errors,400);
      }
     $pp = PersonProduct::where('person_id',$request['person_id'])->where('product_id',$request['product_id'])->first();
     $person = Person::find($request['person_id']);
     $product = Product::find($request['product_id']);
     if($pp === null) {
      if($product->is_gold_product){
        $lp = $product->geoDefaultLivehoodPoints($person->geography_id);
        if($lp){
          $request['product_lp'] = $lp;
        }else{
          $default_livehood_points = $product->calc_raw_material_cost + ($product->calc_wage_applicable * $product->calc_hours_worked );
         $points = $default_livehood_points + $default_livehood_points * ($product->calc_margin_applicable /100);
          $request['product_lp'] = $points;
        }
      }else{
        $default_livehood_points = $request['calc_raw_material_cost'] + ($request['calc_wage_applicable']*$request['calc_hours_worked'] );
         $points = $default_livehood_points + $default_livehood_points*($request['calc_margin_applicable']/100);
         $request['product_lp'] = $points;
      }        
      $pp = $this->repository->create($request->all());
      $template_id = 1207161761333966067;
      sendSMS('New product '.$pp->product->name.' with quantity '.$pp->quantity_available.' '.$pp->unit->name.' has been added to your account by DM.',$person->mobile,$template_id);   
      return response()->json($pp->load('person.personPersonalDetails','product.units'),201); 
     }else {
      return response()->json(['error'=>'Product Already Added'],406);
     }
     
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\PersonProduct  $personProduct
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {


        return response()->json($this->repository->findById($id)->load('geography','unit','product','drishteeMitra','person'),200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\PersonProduct  $personProduct
     * @return \Illuminate\Http\Response
     */
    public function edit(PersonProduct $personProduct)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\PersonProduct  $personProduct
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validation = Validator::make($request->all(),[
          'geography_id'            => 'required|exists:geographies,id',
          'geography_type'          => 'required' ,
          'dm_id'                   => 'required|exists:drishtree_mitras,id',
          'person_id'               => 'required|exists:people,id',
          'product_id'              => 'required|exists:products,id',
          'unit_id'                 => 'required|exists:units,id',
          'quantity_available'      => 'required',
          'product_lp'              => 'required',
          'active_on_barterplace'   => 'required|boolean',
          'calc_raw_material_cost'  => 'required|numeric',
          'calc_wage_applicable'    => 'required|numeric',
          'calc_hours_worked'       => 'required|numeric',
          'calc_margin_applicable'  => 'required|numeric'
      ]);
      if($validation->fails()) {
        $errors =$validation->errors();
        return response()->json($errors,400);
      }
      $person = Person::find($request['person_id']);

      $pp = PersonProduct::where('person_id',$request['person_id'])->where('product_id',$request['product_id'])->first();
     // $person = Person::find($request['person_id']);
     $product = Product::find($request['product_id']);
     // if($pp === null) {
      if($product->is_gold_product){
        $lp = $product->geoDefaultLivehoodPoints($person->geography_id);
        if($lp){
          $request['product_lp'] = $lp;
        }else{
          $default_livehood_points = $product->calc_raw_material_cost + ($product->calc_wage_applicable * $product->calc_hours_worked );
         $points = $default_livehood_points + $default_livehood_points * ($product->calc_margin_applicable /100);
          $request['product_lp'] = $points;
        }
      }else{
        $default_livehood_points = $request['calc_raw_material_cost'] + ($request['calc_wage_applicable']*$request['calc_hours_worked'] );
         $points = $default_livehood_points + $default_livehood_points*($request['calc_margin_applicable']/100);
         $request['product_lp'] = $points;
      }        
      $pp = $this->repository->update($request->all(),$id);
      $template_id = 1207161761342757440;
      sendSMS('Product '.$pp->product->name.' with quantity '.$pp->quantity_available. ' ' .$pp->unit->name. ' has been updated.',$person->mobile,$template_id);  
      return Response()->json($pp,201);
     // }else {
      // return response()->json(['error'=>'Product Already Added'],406);
     // }











     //  $default_livehood_points = $request['calc_raw_material_cost'] + ($request['calc_wage_applicable']*$request['calc_hours_worked'] );
     //  $points = $default_livehood_points + $default_livehood_points*($request['calc_margin_applicable']/100);

     //  $request['product_lp'] = $points;
     //  $pp = $this->repository->update($request->all(),$id);
     //  sendSMS('Product '.$pp->product->name.' with quantity '.$pp->quantity_available. ' ' .$pp->unit->name. ' has been updated.',$person->mobile);
     // return Response()->json($pp,201);
    
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\PersonProduct  $personProduct
     * @return \Illuminate\Http\Response;
     */
    public function destroy(PersonProduct $personProduct)
    {
        //
    }

    /**
    * @param  \App\PersonProduct $person_id
    * @return \App\PersonProduct $p
    * do get list of PersonProduct
    */
    public function personProductGet($person_id){
      $pp = PersonProduct::where('person_id',$person_id)->get();
      $res = collect();
      foreach ($pp as $p) {
        $p->name = $p->product && $p->product->name ? $p->product->name : null;
        $p->photo_path = $p->product && $p->product->photo_path?  $p->product->photo_path: null;
        $p->unit_name = $p->unit && $p->unit->name ? $p->unit->name : null; 
        $p->short_name = $p->unit && $p->unit->short_name ?  $p->unit->short_name : null;
        
        $p->product_alias = $p->product->productAlias; 
        // unset($p->product->);
        // unset($p->unit);
        $res->push($p);
      }
      return response()->json($res,200);
    }

    /**
    * @param  \App\PersonProduct $person_id
    * @return \App\PersonProduct $tejasProducts
    * do get list of tejas PersonProduct
    */
    public function getPersonTejasProducts($person_id){

      $ptp = PersonProduct::where('person_id', $person_id)->get();

      if(!empty($ptp)){
        $tejasProducts = [];

        foreach ($ptp as $product) {
          $tejas_product = null;
          unset($tejas_product);
          if($product->product->is_gold_product){
            
            $tejas_product = $product->product;
            $tejas_product->product_category = $product->product->productCategory;
            $tejas_product->user_added = $product->product->userAdded;
            unset($tejas_product->product_category_id);
            unset($tejas_product->added_by_user_id);
            array_push($tejasProducts, $tejas_product);
          }
        }
         return response()->json($tejasProducts,200);
      }
      return response()->json(["error"=>"Product Not Found."],404);
    }

    /**
    1) Its currently getting list of all persons attached to DM via dm_id
    2) Get all persons from a given geography_id
    */
    public function getPersonList($dm_id){
        $mitra = DrishteeMitra::find($dm_id);
        $persons = Person::where('geography_id',$mitra->person->geography_id)->get();
      //$persons = Person::where('dm_id',$dm_id)->get();
      return response()->json($persons->load('personPersonalDetails'),200); 
    }

    /**
    * @param  \App\Person $person_id
    * @param  \App\DrishteeMitra $dm_id
    * @return \App\PersonProduct $p
    * do get list of PersonProduct related to $person_id and $dm_id and with product.productCategory','product.units','unit','person.personPersonalDetails
    */
    public function getPersonProductList($dm_id,$person_id){
      //  $PersonProduct = PersonProduct::where('person_id',$person_id)->where('dm_id',$dm_id)->get();
        $PersonProduct = PersonProduct::where('person_id',$person_id)->get();
        
        $products = [];
        foreach ($PersonProduct as $pp) {
          if($pp->product->is_gold_product){
            array_push($products, $pp->load('product.productCategory','product.units','unit','person.personPersonalDetails'));
          }
        }
        return response()->json($products,200);
        // return response()->json($PersonProduct->load('product.productCategory','product.units','unit','person.personPersonalDetails'),200);
    }

    /**
    * @param  \App\DrishteeMitra $dm_id
    * @param  \App\PersonProduct $person_id
    * @param  \App\Product $product_id
    * @return \App\PersonProduct $personProduct
    * do get of PersonProduct related to $person_id and $product_id, with product.productCategory','product.units','unit','person.personPersonalDetails
    */
    public function getProduct($dm_id, $person_id, $product_id){
      $personProduct = PersonProduct::where('person_id',$person_id)->where('product_id',$product_id)->first();
      return response()->json($personProduct->load('product.productCategory','product.units','unit','person.personPersonalDetails'),200);
    }

     /**
    * @param  \App\DrishteeMitra $dm_id
    * @param  \App\PersonProduct $person_id
    * @param  \App\Product $product_id
    * @return \App\PersonProduct $personProduct
    * do get list of tejas PersonProduct
    */
    public function getTejasProduct($dm_id, $person_id, $product_id){
      $personProduct = PersonProduct::where('person_id',$person_id)->where('product_id',$product_id)->first();
      return response()->json($personProduct->load('product.productCategory','product.units','unit','person.personPersonalDetails'),200);
    }

    /**
    * @param  \App\DrishteeMitra $dm_id
    * @return \App\PersonProduct $personProduct
    * do get list of tejas products
    */
    public function getTejasProducts($dm_id){
      $drishteeMitra = DrishteeMitra::find($dm_id);
      $personProducts = PersonProduct::where('person_id',$drishteeMitra->person_id)->get();
      $res = collect();
      foreach ($personProducts as $pp) {
        if($pp->product && $pp->product->is_gold_product) {
          $res->push($pp->load('product.productCategory','product.units','unit','person.personPersonalDetails'));
        }
      }
      return response()->json($res,200);
    }

    /**
    * @param  \App\DrishteeMitra $dm_id
    * @return \App\Person $persons
    * do get list of Person with personPersonalDetails
    */
    public function getSellPersonList($dm_id){
        $persons = Person::where("dm_id", $dm_id)->get();
        return response()->json($persons->load('personPersonalDetails'),200);
    }

    /**
    * @param  \App\DrishteeMitra $dm_id
    * @return \App\PersonProduct $res
    * do get list of tejas products with product.productCategory','product.units','unit','person.personPersonalDetails
    */
    public function getProductList($dm_id){
      $drishteeMitra = DrishteeMitra::find($dm_id);
      $personProducts = PersonProduct::where('person_id',$drishteeMitra->person_id)->get();
      $res = collect();
      foreach ($personProducts as $pp) {
        if($pp->product && $pp->product->is_gold_product) {
          $res->push($pp->load('product.productCategory','product.units','unit','person.personPersonalDetails'));
        }
      }
      return response()->json($res,200);
    }

    /**
    * @param  \App\DrishteeMitra $dm_id
    * @param  \App\Product $product_id
    * @return \App\PersonProduct $PersonProduct
    * do get PersonProduct with product.units','product.productCategory','unit','person.personPersonalDetails
    */
    public function getDrishteeProduct($dm_id, $product_id){
      $PersonProduct = PersonProduct::where('product_id',$product_id)->first();
      return response()->json($PersonProduct->load('product.units','product.productCategory','unit','person.personPersonalDetails'));
    }
}
