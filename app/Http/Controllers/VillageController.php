<?php

namespace App\Http\Controllers;

use App\Village;
use Illuminate\Http\Request;
use App\Repositories\Repository\VillageRepository;
use App\Http\Requests\VillageRequest;
// use App\Repositories\Repository\VillageRepository
class VillageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private $repository;

    public function __construct(VillageRepository $repository)
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
    public function store(VillageRequest $request)
    {
        // dd($request->all());
        return response()->json($this->repository->create($request->all(), 201));
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Village  $village
     * @return \Illuminate\Http\Response
     */
    public function show(Village $village)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Village  $village
     * @return \Illuminate\Http\Response
     */
    public function edit(Village $village)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Village  $village
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,Village $village)
    {
        return response()->json($this->repository->update($request->all(), $village->id), 201);
    
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Village  $village
     * @return \Illuminate\Http\Response
     */
    public function destroy(Village $village)
    {
        $village = $this->repository->changeActiveStatus($village->id);
        return response()->json($village,200);
    }

    public function deleteVillage(Request $request,$id)
    {
        $village = Village::find($id);
        if($village){
          $blocks = $village->blocks;
          if($blocks) {
                return response()->json(['error'=>'you can not delete this Village'],400);
          }
          else {
            $village->destroy($id);
            return response()->json(true,201);
          }
        }
        return response()->json(['error'=>'Village Not Found'],404);
    }
}
