<?php

namespace App\Http\Controllers;

use App\Dispute;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Validator;
use Auth;
use App\DisputeComment;

class DisputeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // return response()->
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
     * @return \App\Dispute $dispute
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'barter_id'               => 'required|exists:barters,id',
            'name'                    => 'required',
            'added_by'                => 'required|exists:drishtree_mitras,id',
            'date_added'              => 'sometimes',
            'status'                  => 'sometimes'
        ]);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,400);
        }
        $request['date_added'] = Carbon::now();
        $dispute = Dispute::create($request->all());
        return response()->json($dispute->load('barter','addedBy','comments'),201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Dispute  $dispute
     * @return \Illuminate\Http\Response
     */
    public function show(Dispute $dispute)
    {
        $dispute = Dispute::find($dispute->id);
        return response()->json($dispute->load('barter','addedBy','comments'),200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Dispute  $dispute
     * @return \Illuminate\Http\Response
     */
    public function edit(Dispute $dispute)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Dispute  $dispute
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Dispute $dispute)
    {
        $validation = Validator::make($request->all(),[
            'barter_id'               => 'required',
            'name'                    => 'required',
            'added_by'                => 'required|exists:drishtree_mitras,id',
            'date_added'              => 'sometimes',
            'status'                  => 'sometimes'
        ]);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,400);
        }

        $dispute = Dispute::find($dispute->id);
        if($dispute) {
             $dispute->update($request->all());
        return response()->json($dispute,201);
             
        }
        return response()->json(['error'=>'Dispute Not Found'],404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Dispute  $dispute
     * @return \Illuminate\Http\Response
     */
    public function destroy(Dispute $dispute)
    {
        //
    }

    /**
     * @param \App\DrishteeMitra $dm_id
     * @return \App\Dispute $disputes
     * 
     */
    public function getDmAllDispute($dm_id)
    {
        $disputes = Dispute::where('added_by',$dm_id)->get();
        return response()->json($disputes->load('barter','addedBy','comments'),200);
    }

    /**
     * @return \App\Dispute $disputes
     * do find dispute related to same admin geographies
     */
    public function getDisputeForAdmin()
    {
        $user = Auth::User();
        $user_geographies = $user->userGeographies;
        // return response()->json($user->load('userGeographies.dispute'),200);
        $res = collect();
        foreach ($user_geographies as $ug) {
           $disputes = Dispute::where('geography_id',$ug->geography_id)->get();
           foreach ($disputes as $dis) {
              $res->push($dis); 
           }
           // $res->push($disputes); 
        }
        return response()->json($res,200);
    }

    /**
    * @param \App\Dispute $dispute_id
    * @return \App\Dispute $dispute
    * do load object of barter, comments, geography related to $dispute
    */
    public function getSingleDisputeForAdmin($dispute_id){
        $dispute = Dispute::find($dispute_id);
        return response()->json($dispute->load('barter','comments','geography'),200);
    }

    /**
    * @param  \Illuminate\Http\Request  $request
    * @param \App\Dispute $id
    * @return \App\Dispute $dispute
    * do change status of dispute related to $id
    */
    public function statusChange(Request $request,$id)
    {
        $dispute = Dispute::find($id);
        if($dispute->status == 'Open') {
            if($request['status'] == 'Resolve & No action' || $request['status'] == 'Reject') {
              $dispute->status = $request['status'];
              $dispute->save();   
            }
            else if($request['status'] == 'Resolve & Reverse') {
              $dispute->status = $request['status'];
              $dispute->save();    
            }
        }
        return response()->json($dispute,200);
    }

    /**
    * @param  \Illuminate\Http\Request  $request
    * @param \App\Dispute $dispute_id
    * @return \App\DisputeComment $disputeComment
    * do comment on dispute and return with bater, dispute objects
    */
    public function comment(Request $request, $dispute_id){
         $validation = Validator::make($request->all(),[
            'comment'               => 'required',
            'status'                => 'required',
        ]);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,400);
        }
        $dispute = Dispute::find($dispute_id);
        $request['barter_id'] =  $dispute->barter_id;
        $request['dispute_id'] = $dispute->id;
        
        $disputeComment = DisputeComment::create($request->all());

        return response()->json($disputeComment->load('barter','dispute'),200);

    }
}
