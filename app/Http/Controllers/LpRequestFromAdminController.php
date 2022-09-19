<?php

namespace App\Http\Controllers;

use App\LpRequestFromAdmin;
use Illuminate\Http\Request;
use Validator;
use App\User;
use App\Ledger;

class LpRequestFromAdminController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      return response()->json(LpRequestFromAdmin::all()->load(''));
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
          'admin_id'                   => 'required|exists:users,id',
          'superadmin_approver_id'     => 'sometimes',
          'points_needed'              => 'required',
          'status'                     => 'sometimes'
        ]);
        if($validation->fails())
         {
            $errors = $validation->errors();
            return response()->json($errors,400);
         }
        $lrfd = LpRequestFromAdmin::create($request->all());
        return response()->json($lrfd,200);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\LpRequestFromAdmin  $lpRequestFromAdmin
     * @return \Illuminate\Http\Response
     */
    public function show(LpRequestFromAdmin $lpRequestFromAdmin)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\LpRequestFromAdmin  $lpRequestFromAdmin
     * @return \Illuminate\Http\Response
     */
    public function edit(LpRequestFromAdmin $lpRequestFromAdmin)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\LpRequestFromAdmin  $lpRequestFromAdmin
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
        $validation = Validator::make($request->all(),[
          'admin_id'                   => 'required|exists:users,id',
          'superadmin_approver_id'     => 'sometimes',
          'points_needed'              => 'required',
          'status'                     => 'sometimes'
        ]);
        if($validation->fails())
         {
            $errors = $validation->errors();
            return response()->json($errors,400);
         }

        $lrfd = LpRequestFromAdmin::find($id);
        if($lrfd){
            $lrfd->update($lrfd);
            return response()->json($lrfd,200);
        }
       return response()->json(['error'=>'Not Found'],404); 
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\LpRequestFromAdmin  $lpRequestFromAdmin
     * @return \Illuminate\Http\Response
     */
    public function destroy(LpRequestFromAdmin $lpRequestFromAdmin)
    {
        //
    }
    
    /**
    * @param  \Illuminate\Http\Request  $request
    * @param  \App\LpRequestFromAdmin $id
    * @return \App\LpRequestFromAdmin $lrfds
    * do change status of LpRequestFromAdmin and create transaction
    */
    public function statusChange(Request $request,$id){
        $lrfd = LpRequestFromAdmin::find($id);
        if($lrfd){
            $lrfd->status = $request['status'];
            $lrfd->superadmin_approver_id = $request['user_id'];
            if($request['status'] == 'Accepted') {
                $user= User::find($request['user_id']);
                $ledger = Ledger::find($user->ledger_id);
                if($ledger) {
                 $balance = $ledger->balance - $lrfd->points_needed;    
                 $ledger->balance = $balance;
                 $ledger->save();
                 $lrfd->createLpRequestLedgerTransaction('Success',$ledger->id,'Dr',$lrfd->points_needed,'Lp from superadmin',$ledger->balance);
                 $admin = User::find($lrfd->admin_id);
                 // dd($admin);
                 $admin_ledger = Ledger::find($admin->ledger_id);
                 // dd($admin_ledger);
                 $admin_bal = $admin_ledger->balance + $lrfd->points_needed;
                 $admin_ledger->balance = $admin_bal;
                 $admin_ledger->save();
                 $lrfd->createLpRequestLedgerTransaction('Success',$admin_ledger->id,'Cr',$lrfd->points_needed,'Lp from superadmin',$admin_ledger->balance+$lrfd->points_needed); 
                }
            }
            $lrfd->save();
            return response()->json($lrfd,200);
        }
        return response()->json(['error'=>'Not Found'],404);
    }
    

    /**
    * @return \App\LpRequestFromAdmin $lrfds
    * do get all pending request with requestedByAdmin, approvedBySuperAdmin
    */
    public function getAllPendingRequest(){
        $lrfds = LpRequestFromAdmin::where('status','Open')->get();
        return response()->json($lrfds->load('requestedByAdmin','approvedBySuperAdmin'),200);
    }

    /**
    * @param  \App\LpRequestFromAdmin $id
    * @return \App\LpRequestFromAdmin $lrfds
    * do get all request with requestedByAdmin, approvedBySuperAdmin
    */
    public function getAllRequest($id){
        $lrfds = LpRequestFromAdmin::where('admin_id',$id)->get();
        return response()->json($lrfds->load('requestedByAdmin','approvedBySuperAdmin'),200);
    }
}
