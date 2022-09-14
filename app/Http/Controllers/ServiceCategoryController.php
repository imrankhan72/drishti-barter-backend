<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ServiceCategorySaveRequest;
use App\Repositories\Repository\ServiceCategoryRepository;
use App\ServiceCategory;
use File;
use Storage;
use Validator;
class ServiceCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private $repository;

    public function __construct(ServiceCategoryRepository $repository)
    {
        $this->repository = $repository;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
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
    public function store(ServiceCategorySaveRequest $request)
    {
        //
        return response()->json($this->repository->create($request->all(), 201));

    }
    /**
     * Display the specified resource.
     *
     * @param  \App\State  $state
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\State  $state
     * @return \Illuminate\Http\Response
     */
    public function edit(ServiceCategory $servicecategory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\State  $state
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        return response()->json($this->repository->update($request->all(), $id), 201);
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\State  $state
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $sc= ServiceCategory::find($id);
        if($sc) {
          $sc->destroy($id);
          return response()->json($sc,201); 
        }
        return response()->json(['error'=>'SC Not Found'],404);
    }

    /**
     *
     * @param  \App\ServiceCategory $id
     * @return \App\ServiceCategory $servicecategory
     * do change service category status
     */
    public function changeActiveStatus($id){
        $servicecategory = $this->repository->changeActiveStatus($id);
        return response()->json($servicecategory,200);
    }

    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ServiceCategory $id
     * @return \App\ServiceCategory $sc
     * do upload service category image
     */
    public function uploadImage(Request $request,$id){
        // dd($request['file']);
       //$this->authorize('create',StaffBasicDetails::class);
       $validation = Validator::make($request->all(),[
            'file' => 'required|file|mimes: jpg,jpeg,png,bmp|max:5000'
        ]);
        if($validation->fails()){
            $errors = $validation->errors();
            return response()->json($errors, 400);
        }
       $sc = ServiceCategory::find($id);
       $file = $request['file']->getClientOriginalName();
       $extension = $request['file']->getClientOriginalExtension(); 
       $filename = pathinfo($file, PATHINFO_FILENAME);
       $originalName = $filename.'.'.$extension; 
       $cover = $request->file('file');
       Storage::disk('public')->put($originalName,File::get($cover));
       $request['icon_name'] = $originalName;
       $request['icon_path'] = Storage::disk('public')->url($originalName);
       $sc->update($request->only(['icon_name','icon_path']));
       return response()->json($sc,201); 
    }
}
