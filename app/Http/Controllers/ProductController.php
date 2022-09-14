<?php

namespace App\Http\Controllers;

use App\Product;
use Illuminate\Http\Request;
use App\Http\Requests\ProductSaveRequest;
use App\Repositories\Repository\ProductRepository;
use Auth;
use Validator;
use App\MarginPercentage;
use Storage;
use File;
use Carbon\Carbon;
use Rap2hpoutre\FastExcel\FastExcel;
use App\Unit;
use App\ProductCategory;
use App\ProductAlias;
use App\PersonProduct;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private $repository;
    public function __construct(ProductRepository $repository)
    {
        $this->repository  = $repository;
    }

    public function filteredProducts(Request $request)
    {
        $contacts = Product::filterProducts($request);
        return response()->json($contacts,200);
    }

    public function index(Request $request)
    {
        $products = Product::filterProducts($request);
         
        return response()->json($products,200);
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
      // dd('he');

        // edit by arunCobild
         $validation = Validator::make($request->all(),[
            'name'                          => 'required|string',
            'product_category_id'           => 'required|exists:product_categories,id',
            'calc_raw_material_cost'        => 'required|numeric',
            'calc_wage_applicable'          => 'required|numeric',
            'calc_hours_worked'             => 'required|numeric',
            'calc_margin_applicable'        => 'required|numeric'
        ]);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,400);
        }


        $margin = MarginPercentage::first();


        $default_livehood_points = $request['calc_raw_material_cost'] + ($request['calc_wage_applicable']*$request['calc_hours_worked'] );
         $points = $default_livehood_points + $default_livehood_points*($request['calc_margin_applicable']/100);

        $request['default_livehood_points'] = $points;
      return response()->json($this->repository->create($request->all()),201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        return response()->json($this->repository->findById($product->id)->load('geographyProduct.geography','productCategory','approvedBy','units','productAlias','userAdded'),201);       
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $validation = Validator::make($request->all(),[
            'name'                          => 'required|string',
            'product_category_id'           => 'required|exists:product_categories,id',
            'calc_raw_material_cost'        => 'required|numeric',
            'calc_wage_applicable'          => 'required|numeric',
            'calc_hours_worked'             => 'required|numeric',
            'calc_margin_applicable'        => 'required|numeric'
        ]);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,400);
        }


        $margin = MarginPercentage::first();


        $default_livehood_points = $request['calc_raw_material_cost'] + ($request['calc_wage_applicable']*$request['calc_hours_worked'] );
         $points = $default_livehood_points + $default_livehood_points*($request['calc_margin_applicable']/100);

        $request['default_livehood_points'] = $points;
      // return response()->json($this->repository->create($request->all()),201);
        $data = $this->repository->update($request->all(), $product->id);


   // Once
       // $products = Product::all();
       // foreach ($products as $p) {
       //     $personProducts = PersonProduct::where('product_id',$p->id)->get();
       //     foreach ($personProducts as $pp) {
       //             $pp->product_lp =  $p->default_livehood_points;
       //             $pp->save();
       //          }     
       //  }
 // always
        $per_products = PersonProduct::where('product_id',$product->id)->get();
        foreach ($per_products as $pro) {
             $pro->product_lp = $request['default_livehood_points'];
             $pro->save(); 
        }


 //       

        // $products = PersonProduct::all();
        // foreach ($products as $p) {
        //     $lp = PersonProduct::where("product_id",$p->id)->first();
        //     if($lp){
        //         foreach ($p->personProduct as $pp) {
        //             $pp->product_lp = $lp->product_lp;
        //             $pp->save();
        //         }
        //     }
        // }

        return response()->json($data, 201);
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Product $product
     * do import product and create ProductAlias, category
     */
    public function importProducts(Request $request){ 
        $regex = "/^(?=.+)(?:[1-9]\d*|0)?(?:\.\d+)?$/";
        $validation = Validator::make($request->all(),[
            '*.name'                          => 'required|string',
            '*.product_category_name'         => 'required|string',
            '*.calc_raw_material_cost'        => array('required','regex:'.$regex),
            '*.calc_wage_applicable'          => array('required','regex:'.$regex),
            '*.calc_hours_worked'             => array('required','regex:'.$regex),
            '*.calc_margin_applicable'        => array('required','regex:'.$regex),
          //  '*.calc_margin_percentage'        => array('required','regex:'.$regex),
            '*.is_gold_product'               => 'required|boolean',
            '*.unit_name'                     => 'required|string'
        ]);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,400);
        }        

        $coll_insert = collect();
        $coll_update = collect();
        $coll_alias = collect();
        foreach ($request->all() as $data) {
            $product = Product::where('name',$data['name'])->first();
            
            $pc = ProductCategory::where('name',$data['product_category_name'])->first();
            if(!$pc){
                $pc = new ProductCategory();   
                $pc->name = $data['product_category_name'];
                $pc->save();
            }
            $data['product_category_id'] = $pc->id;

            $unit = Unit::where('name',$data['unit_name'])->first();
            if($unit){
                $data['unit_id'] = $unit->id;
            }else{
                $unit = new Unit();   
                $unit->name = $data['unit_name'];
                $unit->short_name = $data['unit_name'];
                $unit->conversion_factor = '1';
                $unit->save();
                $data['unit_id'] = $unit->id;
            }

            $margin = MarginPercentage::first();
          //  $data['default_livehood_points'] = $data['calc_raw_material_cost'] + ($data['calc_wage_applicable']*$data['calc_hours_worked'] )+ $margin->margin_percentage;
            $data['calc_margin_percentage'] = $data['calc_margin_applicable'];
            $default_livehood_points = $data['calc_raw_material_cost'] + ($data['calc_wage_applicable']*$data['calc_hours_worked'] );
          //  dd($request['calc_wage_applicable']*$request['calc_hours_worked']);
         $points = $default_livehood_points + $default_livehood_points*($data['calc_margin_percentage']/100);
        // dd($request['calc_margin_percentage']);
         $data['default_livehood_points'] = $points;
            if($product){
                $product->update($data);
                $coll_update->push($product);
            }else{
                $user = Auth::User();
                $data['added_by_user_id'] = $user->id;    
                // $data['added_by_user_id'] = 1;
                $product = Product::create($data);
                $coll_insert->push($product);
            }

           if(!empty($data['product_translation']) && !empty($data['language']) && (sizeof($data['product_translation']) == sizeof($data['language']))) {
                $array_proTran = $data['product_translation'];
                $array_lan = $data['language'];
                $i=0;
                foreach ($array_proTran as $pt) {
                    $pa = ProductAlias::where('product_id',$product->id)->where('language',$array_lan[$i])->where('product_translation',$pt)->first();
                    if(!$pa){
                        $paa['product_id'] = $product->id;
                        $paa['product_translation'] = $pt;
                        $paa['language'] = $array_lan[$i];
                        $pa = ProductAlias::create($paa);
                        $coll_alias->push($pa);
                    }
                    $i++;
                }                
           }
        } 
        return response()->json(["Products Insert"=>$coll_insert,"Products Update"=>$coll_update,"Products Alias"=>$coll_alias],200);
    }

    /**
     * @return download url of productexport file
     */
    public function exportProducts(){
        $products = Product::all();
        $file = Carbon::now()->format('YmdHis').'products.xlsx';
        $filepath = (new FastExcel($products))->export(storage_path().'/'.$file);
        $url = "api/productsexport/".$file;
        return response()->json(url($url),200);
    }

    /**
     * @param  $filename
     * @return download file
     */
    public function downloadExportProducts($filename){
        $file = basename($filename);
        $filepath = storage_path().'/'.$file;
        return response()->download($filepath, $file, [
            'Content-Length: '. filesize($filepath)
        ]);
    }

    /**
     *
     * @param  $filename
     * @return download url of product import sample file
     */
    public function importSamplefile(){
        $file = 'ProductSamplefile.xlsx';
        $url = "api/product/samplefile/".$file;
        return response()->json(url($url),200);
    }

    /**
     *
     * @param  $filename
     * @return download file
     */
    public function downloadImportSamplefile($filename){
        $file = basename($filename);
        $filepath = storage_path().'/'.$file;
        return response()->download($filepath, $file, [
            'Content-Length: '. filesize($filepath)
        ]);
    }

    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @param \App\Product $id
     * @return \App\Product $pc
     * do upload product images
     */
    public function productAlias(Request $request){
        $validation = Validator::make($request->all(),[
           'product_id'          => 'required|exists:products,id',
           'product_translation' => 'required',
           'language'            => 'required'
        ]);
        if($validation->fails()) {
            $error = $validation->errors();
            return response()->json($error,400);
        }
        $pp = Product::find($request['product_id']);
        if($pp) {
            $pa = $pp->productAlias()->create($request->all());
            return response()->json($pa,201);
        }
        return response()->json(['error'=>'Product Not Found'],404);
    }


    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @param \App\Product $id
     * @return \App\Product $pc
     * do upload product images
     */
    public function uploadImage(Request $request,$id){
        // dd($request['file']);
       //$this->authorize('create',StaffBasicDetails::class);
       $validation = Validator::make($request->all(),[
            'file' => 'required|file|mimes: jpg,jpeg,png,bmp'
        ]);
        if($validation->fails()){
            $errors = $validation->errors();
            return response()->json($errors, 400);
        }
       $pc = Product::find($id);
       $file = $request['file']->getClientOriginalName();
       $extension = $request['file']->getClientOriginalExtension(); 
       $filename = pathinfo($file, PATHINFO_FILENAME);
       $originalName = $filename.'.'.$extension; 
       $cover = $request->file('file');
       Storage::disk('public')->put($originalName,File::get($cover));
       $request['photo_name'] = $originalName;
       $request['photo_path'] = Storage::disk('public')->url($originalName);
       $pc->update($request->only(['photo_name','photo_path']));
       return response()->json($pc,201); 
    }

    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Product $products
     * do return list of tejas products
     */
    public function getAllTejasProducts(Request $request){
        $products = Product::where('is_gold_product', 1)->get();
        $productsCollection = collect();
        foreach ($products as $product) {
            if($request['geo_id']){
                $lp = $product->geoDefaultLivehoodPoints($request['geo_id']);
                if($lp){
                    $product->default_livehood_points = $lp;
                }
            }
            $productsCollection->push($product);
        }
        return response()->json($productsCollection,200);
    }

    public function deleteProduct($id)
    {
        $product = Product::find($id);
        if($product) {
          $palias = $product->productAlias;
          $gproduct = $product->geographyProduct;
          $pproduct = $product->personProduct;
          $bneedproduct = $product->barterNeedProduct;
          $bmlip = $product->barterMatchLocalInventoryProducts;
          $bsrp = $product->sellRequestProducts;
          if(count($palias) > 0 || count($gproduct) > 0 || count($pproduct) > 0 || count($bneedproduct) > 0 || count($bmlip) > 0 || count($bsrp) > 0) {
                return response()->json(['error'=>'you can not delete this Product'],400);

          }
          else {
            $product->destroy($id);
            return response()->json(true,200);
          }
        }
        return response()->json(['error'=>'Product Not Found'],404);
    }

    public function productCount(Request $request)
    {
        $checktoken = $request->header('checktoken');
          if($checktoken != '0fQgVCL1cM2mGytOSovz') {
           return response()->json('Token Invalid',402);
           }
        $products = Product::all();
        if(isset($request['count']) ) {
            return response()->json(count($products),200);
        }
        return response()->json($products,200);
    }
}
