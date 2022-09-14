<?php

namespace App\Http\Controllers;

use App\City;
use Illuminate\Http\Request;
use App\Http\Requests\CityRequest;
use App\Repositories\Repository\CityRepository;

class CityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private $repository;
    public function __construct(CityRepository $repository)
    {
        $this->repository = $repository;
    }
    public function index()
    {
        return response()->json($this->repository->all()->load('districts'),200);
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
    public function store(CityRequest $request)
    {
        return response()->json($this->repository->create($request->all(), 201));
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\City  $city
     * @return \Illuminate\Http\Response
     */
    public function show(City $city)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\City  $city
     * @return \Illuminate\Http\Response
     */
    public function edit(City $city)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\City  $city
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, City $city)
    {
        return response()->json($this->repository->update($request->all(), $city->id), 201);
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\City  $city
     * @return \Illuminate\Http\Response
     */
    public function destroy(City $city)
    {
        $city = $this->repository->changeActiveStatus($city->id);
        return response()->json($city,200);
    }
    public function deleteCity(Request $request,$id)
    {
        $city = City::find($id);
        if($city) {
          $cdistrict = $city->districts;
          if($cdistrict) {
                return response()->json(['error'=>'you can not delete this city'],400);
          }
          else {
            $city->destroy($id);
            return response()->json(true,201);
          }
        }
        return response()->json(['error'=>'City Not Found'],404);
    }
    public function envUrl()
    {
        dd(env('APP_URL'));
    }
}
