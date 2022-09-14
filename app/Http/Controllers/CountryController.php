<?php

namespace App\Http\Controllers;

use App\Country;
use Illuminate\Http\Request;
use App\Repositories\Repository\CountryRepository;
use App\Http\Requests\CountryRequest;

class CountryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
   private $repository;

    public function __construct(CountryRepository $repository)
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
        //Get country
        return response()->json($this->repository->all()->load('states.districts.blocks.villages','states.districts.city'),200);
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
    public function store(CountryRequest $request)
    {
        //save country
        return response()->json($this->repository->create($request->all(), 201));

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Country  $country
     * @return \Illuminate\Http\Response
     */
    public function show(Country $country)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Country  $country
     * @return \Illuminate\Http\Response
     */
    public function edit(Country $country)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Country  $country
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Country $country)
    {
        return response()->json($this->repository->update($request->all(), $country->id), 201); // Update Country
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Country  $country
     * @return \Illuminate\Http\Response
     */
    public function destroy(Country $country)
    {
      $country = $this->repository->changeActiveStatus($country->id);  // change country status
        return response()->json($country,200);
    }
    public function deleteCountry($id)
    {
      $country = Country::find($id);
      if($country) {
        $cstate = $country->states;
        if(count($cstate)>0 ) {
            return response()->json(['error'=>'Can not delete country'],400);
        }
        else {
            $country->destroy($id);
            return response()->json(true,200);
        } 
      } 
      return response()->json(['error'=>'Country Not Found'],404);       
    }
}
