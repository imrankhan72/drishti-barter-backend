<?php

namespace App\Http\Controllers;

use App\State;
use Illuminate\Http\Request;
use App\Repositories\Repository\StateRepository;
use App\Http\Requests\StateRequest;
// use FastExcel;
use Rap2hpoutre\FastExcel\FastExcel;
use App\ServiceRateList;

class StateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private $repository;

    public function __construct(StateRepository $repository)
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
       
        $s = State::with('stateRateList')->orderBy('name')->get();
        return response()->json($s,200);
        // return response()->json($this->repository->all()->load('stateRateList'),200);
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
    public function store(StateRequest $request)
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
    public function show(State $state)
    {
        return response()->json($this->repository->findById($state->id)->load('stateRateList'), 200);
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\State  $state
     * @return \Illuminate\Http\Response
     */
    public function edit(State $state)
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
    public function update(Request $request, State $state)
    {
        return response()->json($this->repository->update($request->all(), $state->id), 201);
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\State  $state
     * @return \Illuminate\Http\Response
     */
    public function destroy(State $state)
    {
        $state = $this->repository->changeActiveStatus($state->id);
        return response()->json($state,200);
    }
    public function importState(Request $request)
    {
     // dd($request->file('file'));  
      $users = (new FastExcel)->import($request->file('file'), function ($line) {
      return State::create([
        'name' => $line['name'],
        'country_id' => $line['country_id']
      ]);
      });  
    }
    public function deleteState(Request $request,$id)
    {
        $state = State::find($id);
        if($state) {
           $cstate = $state->country;
           $sdistrict = $state->districts;
           $srate = $state->stateRateList;
           $sdm = $state->drishteeMitras;
           $sp = $state->person;
           if($sdistrict || count($sdm)>0 || count($sp)>0) {
                return response()->json(['error'=>'you can not delete this state'],400);
              
           }
           else {
              $srl = ServiceRateList::where('state_id',$id)->first();
              if($srl) {
                $srl->destroy($srl->id);
              }
              $state->destroy($id);
              return response()->json(true,200);
           } 
        }
        return response()->json(['error'=>'State Not Found'],404);
    }
}
