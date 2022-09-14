<?php

namespace App\Http\Controllers;

use App\UserProduct;
use Illuminate\Http\Request;
use Validator;
use App\Product;
use Carbon\Carbon;
use Rap2hpoutre\FastExcel\FastExcel;

class UserProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(UserProduct::all(),200);
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
    public function show(UserProduct $userProduct)
    {
        //
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
    public function report(Request $request)
    {
      // dd("ge");
      $userproducts = null;
      if(isset($request['user_id'])) {
        $userproducts =UserProduct::where('user_id',$request['user_id'])->get();  
      }
      else {
        $userproducts = UserProduct::all();

      }
      // dd("hge");
      // dd($userproducts);
      $data = UserProduct::transformData($userproducts);
     // return response()->json($data,200);
      $file = Carbon::now()->format('YmdHis').'UserProduct.xlsx';
      $filepath = (new FastExcel($data))->export(storage_path().'/'.$file);
      $url = "api/productsexport/".$file;
       return response()->json(url($url),200);


    }
}
