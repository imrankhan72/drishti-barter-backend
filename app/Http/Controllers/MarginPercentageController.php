<?php

namespace App\Http\Controllers;

use App\MarginPercentage;
use Illuminate\Http\Request;
use App\Repositories\Repository\MarginPercentageRepository;
use Validator;
class MarginPercentageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private $repository;
    public function __construct(MarginPercentageRepository $repository)
    {
        $this->repository = $repository;
    }
    public function index()
    {
        return response()->json(MarginPercentage::first(),200);
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
          'margin_percentage' => 'required'
        ]);
        if($validation->fails())
        {
          $errors = $validation->errors();
          return response()->json($errors,400);
        }
        $mp = MarginPercentage::first();
        if($mp) {
            $mp->update($request->all());

        }
        else {
           $mp= MarginPercentage::create($request->all()); 
        }
        return response()->json($mp,201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\MarginPercentage  $marginPercentage
     * @return \Illuminate\Http\Response
     */
    public function show(MarginPercentage $marginPercentage)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\MarginPercentage  $marginPercentage
     * @return \Illuminate\Http\Response
     */
    public function edit(MarginPercentage $marginPercentage)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\MarginPercentage  $marginPercentage
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, MarginPercentage $marginPercentage)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\MarginPercentage  $marginPercentage
     * @return \Illuminate\Http\Response
     */
    public function destroy(MarginPercentage $marginPercentage)
    {
        //
    }
}
