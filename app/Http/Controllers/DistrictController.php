<?php

namespace App\Http\Controllers;

use App\District;
use Illuminate\Http\Request;
use App\Http\Requests\DistrictRequest;
use App\Repositories\Repository\DistrictRepository;

class DistrictController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    private $repository;
    public function __construct(DistrictRepository $repository)
    {
        $this->repository = $repository;
    }
    public function index()
    {
        $districts = District::orderBy('name')->get();
        return response()->json($districts,200);
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
    public function store(DistrictRequest $request)
    {
        return response()->json($this->repository->create($request->all(), 201));
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\District  $district
     * @return \Illuminate\Http\Response
     */
    public function show(District $district)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\District  $district
     * @return \Illuminate\Http\Response
     */
    public function edit(District $district)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\District  $district
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, District $district)
    {
        return response()->json($this->repository->update($request->all(), $district->id), 201);
      
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\District  $district
     * @return \Illuminate\Http\Response
     */
    public function destroy(District $district)
    {
        $district = $this->repository->changeActiveStatus($district->id);
        return response()->json($district,200);
    }
    public function getDistrictByStateId($state_id)
    {
        $districts = District::where('state_id',$state_id)->get();
        return response()->json($districts,200);
    }
    public function deleteDistrict(Request $request,$id)
    {
        $district = District::find($id);
        if($district) {
           $dcity = $district->city;
           $dblocks = $district->blocks;
           $dstates = $district->states;
           if(count($dcity)>0 || count($dblocks)>0 || count($dstates)>0) {
                return response()->json(['error'=>'you can not delete this district'],400);

           } 
           else {
            $district->destroy($id);
              return response()->json(true,200);
            
           }   
        }
        return response()->json(['error'=>'District Not Found'],404);
    }
}
