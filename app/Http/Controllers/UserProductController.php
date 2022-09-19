<?php

namespace App\Http\Controllers;

use App\UserProduct;
use Illuminate\Http\Request;
use Validator;
use App\Product;
use Carbon\Carbon;
use Rap2hpoutre\FastExcel\FastExcel;
use App\UserProductLog;
use App\State;
use App\Geography;
use App\UserGeography;

class UserProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(UserProduct::all()->load('userProductLog'),200);
    }
    public function test()
    {
      dd("hello ji im here where are you?");
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
          'user_id'               => 'required|exists:users,id',
          'product_id'              => 'required|exists:products,id',
         // 'unit_id'                 => 'sometimes|exists:units,id',
          'quantity_available'      => 'required',
         // 'product_lp'              => 'required',
      ]);
      if($validation->fails()) {
        $errors =$validation->errors();
        return response()->json($errors,400);
      }
      $product = Product::find($request['product_id']);
      $request['product_lp'] = $product->default_livehood_points;
     $up = UserProduct::where('user_id',$request['user_id'])->where('product_id',$request['product_id'])->get();
     if(count($up) ==  0) {
        // $person = User::find($request['person_id']);
        $up = UserProduct::create($request->all());
        $up->tag = 'Manually Added';
        $up->save();
      $temp['user_product_id'] = $up->id;
      $temp['product_id'] = $up->product_id;
      $temp['quantity'] = $request['quantity_available'];
      $temp['product_lp'] = $request['product_lp'];
      $temp['message'] = $request['quantity_available'].' Quantity added Manually'; 
      $upl = UserProductLog::create($temp);
      $up->update($request->all());
       // sendSMS('New product '.$pp->product->name.' with quantity '.$pp->quantity_available.' '.$pp->unit->name.' has been added to your account by DM.',$person->mobile);   
        return response()->json($up->load('user','product','unit'),201); 
     }else {
      return response()->json(['error'=>'Product Already Added'],406);
     }
     
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\UserProduct  $userProduct
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $up = UserProduct::find($id);
        return response()->json($up->load('userProductLog','user','product','unit'),200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\UserProduct  $userProduct
     * @return \Illuminate\Http\Response
     */
    public function edit(UserProduct $userProduct)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\UserProduct  $userProduct
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validation = Validator::make($request->all(),[
          'user_id'               => 'required|exists:users,id',
          'product_id'              => 'required|exists:products,id',
        //  'unit_id'                 => 'required|exists:units,id',
          'quantity_available'      => 'required',
          'product_lp'              => 'required'
      ]);
      if($validation->fails()) {
        $errors =$validation->errors();
        return response()->json($errors,400);
      }
      $up = UserProduct::find($id);
      $temp['user_product_id'] = $up->id;
      $temp['product_id'] = $up->product_id;
      $temp['quantity'] = $request['quantity_available'];
      $temp['product_lp'] = $request['product_lp'];
      $temp['message'] = $request['quantity_available'] > $up->quantity_available ? 'Quantity increased with '.($request['quantity_available']- $up->quantity_available): 'Quantity Decreased with '.($up->quantity_available - $request['quantity_available']); 
      $upl = UserProductLog::create($temp);
      $up->update($request->all());
      // sendSMS('Product '.$pp->product->name.' with quantity '.$pp->quantity_available. ' ' .$pp->unit->name. ' has been updated.',$person->mobile);
     return response()->json($up->load('user','product','unit'),201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\UserProduct  $userProduct
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserProduct $userProduct)
    {
        //
    }
    public function report(Request $request){

      $userproducts = [];
      $data = [];
      if(isset($request['user_id'])) {
        $userproducts = UserProduct::where('user_id',$request['user_id'])->get();
      }else if(isset($request['geo_id'])){
        
        $userGeo = UserGeography::where('geography_id', $request['geo_id'])->get();
        foreach ($userGeo as $ug){
          $up = UserProduct::where('user_id',$ug->user_id)->get();
          if(count($up) !=0){
            foreach($up as $u){
              array_push($userproducts, $u);
            }
          }
        }
      }
      else {
        $userproducts = UserProduct::all();
      }

      // return response()->json($userproducts,200);
      $data = UserProduct::transformData($userproducts);
      $file = Carbon::now()->format('YmdHis').'UserProduct.xlsx';
      // return response()->json($data,200);
      $filepath = (new FastExcel($data))->export(storage_path().'/'.$file);
      $url = "api/productsexport/".$file;
      return response()->json(url($url),200);
    }

    public function sellProduct(Request $request)
    {
      $validation = Validator::make($request->all(),[
          'mr_no'           => 'required|unique:user_product_logs,mr_no',
          'user_product_id' => 'required|exists:user_products,id',
          'product_id'      => 'required|exists:products,id',
          'quantity'        => 'required',
          'product_lp'      => 'required',
          'amount'          => 'required'
      ]);
      if($validation->fails()) {
        $errors = $validation->errors();
        return response()->json($errors,400);
      }

  

      // curl_setopt($ch, CURLOPT_URL,"http://vatika.drishtee.in/api/BarterAPI/CheckMRNumber?MRNo=".$request['mr_no']);
      // //curl_setopt($ch, CURLOPT_POSTFIELDS, 'foo=1&bar=2&baz=3');


      // $res = curl_exec($ch);
      // curl_close($ch);



      $data = array();
      $data['MRNo'] = $request['mr_no'];
      $data['Amount'] = $request['amount'];
      $url = 'http://vatika.drishtee.in/api/BarterAPI/CheckMRNumber';
      $ch = curl_init($url);
      $payload = json_encode($data);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLINFO_HEADER_OUT, true);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

      // Set HTTP Header for POST request 
      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($payload))
      );

  // Submit the POST request
      $result = curl_exec($ch);
  // Close cURL session handle
     curl_close($ch);
    // return json_decode($result);
      $res = json_decode($result);
      // return response()->json(,200);
      //dd($res->StatusCode);
      if($res->StatusCode == 200) {
        $up = UserProduct::find($request['user_product_id']);
      if($up->quantity_available >= $request['quantity']) {
      $upl = UserProductLog::create($request->all());
      $up->quantity_available = $up->quantity_available - $request['quantity'];  
      $up->save();
      $upl->message = 'Mr No '.$request['mr_no'].' created for quantity:'.$request['quantity'];
      $upl->save();
      
       }
       else {
        return response()->json(['error'=>'Quantity can not be more than quantity you have'],401);
       }
      return response()->json($up->load('userProductLog'),200);
      } 
     else {
      return response()->json(['error'=>'Either MRNo or Amount not valid'],401);
     }
    }
}
