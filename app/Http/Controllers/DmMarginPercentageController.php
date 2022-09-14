<?php

namespace App\Http\Controllers;

use App\DmMarginPercentage;
use Illuminate\Http\Request;
use Validator;

class DmMarginPercentageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function index()
    {
     return response()->json(DmMarginPercentage::first(),200);      
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
               'dm_margin_percentage'  => 'required'
        ]);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors);
        }
        $dmp= DmMarginPercentage::first();
        if($dmp) {
            $dmp->update($request->except('id'));
        }
        else {
           $dmp = DmMarginPercentage::create($request->all());
            
        }
        return response()->json($dmp,201);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\DmMarginPercentage  $dmMarginPercentage
     * @return \Illuminate\Http\Response
     */
    public function show(DmMarginPercentage $dmMarginPercentage)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\DmMarginPercentage  $dmMarginPercentage
     * @return \Illuminate\Http\Response
     */
    public function edit(DmMarginPercentage $dmMarginPercentage)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\DmMarginPercentage  $dmMarginPercentage
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DmMarginPercentage $dmMarginPercentage)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\DmMarginPercentage  $dmMarginPercentage
     * @return \Illuminate\Http\Response
     */
    public function destroy(DmMarginPercentage $dmMarginPercentage)
    {
        //
    }
}
