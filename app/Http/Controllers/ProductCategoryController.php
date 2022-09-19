<?php

namespace App\Http\Controllers;

use App\ProductCategory;
use Illuminate\Http\Request;
use App\Http\Requests\ProductCategoryRequest;
use App\Repositories\Repository\ProductCategoryRepository;
use Validator;
use Storage;
use File;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private $repository;
    public function __construct(ProductCategoryRepository $repository)
    {
        $this->repository=$repository;
    }
    public function index()
    {
        $pc = ProductCategory::orderBy('name')->get();
        return response()->json($pc,200);
        // return response()->json($this->repository->all(),200);
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
        return response()->json($this->repository->create($request->all(), 201));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ProductCategory  $productCategory
     * @return \Illuminate\Http\Response
     */
    public function show(ProductCategory $productCategory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ProductCategory  $productCategory
     * @return \Illuminate\Http\Response
     */
    public function edit(ProductCategory $productCategory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ProductCategory  $productCategory
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
        return response()->json($this->repository->update($request->all(), $id), 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ProductCategory  $productCategory
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      $pc = ProductCategory::find($id);
      if($pc)
         {
            $pc->destroy($id);
            return response()->json($pc,200);
         }
       return response()->json(['error'=>'PC Not Found'],404);  
    }

    /**
    * @param  \App\ProductCategory $id
    * @return \App\ProductCategory $pc
    * do
    */
    public function deactivatePC($id){
     $pc = ProductCategory::find($id);
      if($pc)
         {
            $pc->is_active = !$pc->is_active;
            $pc->save();
            return response()->json($pc,200);
         }
       return response()->json(['error'=>'PC Not Found'],404);          
    }


    /**
     *
     * @param  \App\ProductCategory $id
     * @return \App\ProductCategory $pc
     * do upload product category images
     */
    public function uploadImage(Request $request,$id)
    {
        // dd($request['file']);
       //$this->authorize('create',StaffBasicDetails::class);
       $validation = Validator::make($request->all(),[
            'file' => 'required|file|mimes: jpg,jpeg,png,bmp|max:5000'
        ]);
        if($validation->fails()){
            $errors = $validation->errors();
            return response()->json($errors, 400);
        }
       $pc = ProductCategory::find($id);
       $file = $request['file']->getClientOriginalName();
       $extension = $request['file']->getClientOriginalExtension(); 
       $filename = pathinfo($file, PATHINFO_FILENAME);
       $originalName = $filename.'.'.$extension; 
       $cover = $request->file('file');
       Storage::disk('public')->put($originalName,File::get($cover));
       $request['icon_name'] = $originalName;
       $request['icon_path'] = Storage::disk('public')->url($originalName);
       $pc->update($request->only(['icon_name','icon_path']));
       return response()->json($pc,201); 
    }
}
