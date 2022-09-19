<?php

namespace App\Http\Controllers;

use App\ProductGeographyAvailability;
use Illuminate\Http\Request;
use App\Repositories\Repository\ProductGeographyAvailabilityRepository;
use App\Product;
use App\Geography;
use Validator;

class ProductGeographyAvailabilityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private $repository;
    public function __construct(ProductGeographyAvailabilityRepository $repository)
    {
        $this->repository = $repository;
    }
    public function index()
    {
      return response()->json($this->repository->all(),200);
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
      return response()->json($this->repository->create($request->all()),200);
    
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ProductGeographyAvailability  $productGeographyAvailability
     * @return \Illuminate\Http\Response
     */
    public function show(ProductGeographyAvailability $productGeographyAvailability)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ProductGeographyAvailability  $productGeographyAvailability
     * @return \Illuminate\Http\Response
     */
    public function edit(ProductGeographyAvailability $productGeographyAvailability)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ProductGeographyAvailability  $productGeographyAvailability
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
        //dd($request->all());
      return response()->json($this->repository->update($request->all(),$id),200);
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ProductGeographyAvailability  $productGeographyAvailability
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
     $gp = ProductGeographyAvailability::find($id);
     if($gp){
        $gp->destroy($id);
        return response()->json(true,201);
     }
     return response()->json(['error'=>'Not Found'],404);   
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Product $product
     * do import product geography
     */
    public function productgeographyImport(Request $request){
        $regex = "/^(?=.+)(?:[1-9]\d*|0)?(?:\.\d+)?$/";
        $validation = Validator::make($request->all(),[
            '*.product_name'              => 'required|string',
            '*.geography_name'            => 'required|string',
            // '*.geography_type'            => 'required|string',
            '*.livelihood_points_override'=> array('required','regex:'.$regex),
        ]);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,400);
        }

        $coll_insert = collect();
        foreach ($request->all() as $data) {
            $product = Product::where('name',$data['product_name'])->first();
            $geography = Geography::where('name',$data['geography_name'])->first();
            // if(!$geography){
            //     $d['name']= $data['geography_name'];
            //     $d['type'] = $data["geography_type"];
            //     $geography = Geography::create($d);

            // }
            if($product && $geography){
                $pg = ProductGeographyAvailability::where('product_id',$product->id)->where('geography_id',$geography->id)->where('geography_type',$geography->type)->where('livelihood_points_override',$data['livelihood_points_override'])->first();
                if(!$pg){
                    $pga['product_id']= $product->id;
                    $pga['geography_id']= $geography->id;
                    $pga['geography_type']= $geography->type;
                    $pga['livelihood_points_override']= $data['livelihood_points_override'];
                    $pg = ProductGeographyAvailability::create($pga);
                    $coll_insert->push($pg);
                }    
            }
        }
        return response()->json(["Insert Datas"=>$coll_insert],200);
    }

    /**
     * @return download sample file url
     * 
     */
    public function importSamplefile(){
        $file = 'ProductGeographySamplefile.xlsx';
        $url = "api/productgeography/samplefile/".$file;
        return response()->json(url($url),200);
    }

    /**
     * @return download file
     */
    public function downloadImportSamplefile($filename){
        $file = basename($filename);
        $filepath = storage_path().'/'.$file;
        return response()->download($filepath, $file, [
            'Content-Length: '. filesize($filepath)
        ]);
    }
}
