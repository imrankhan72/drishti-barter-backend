<?php

namespace App\Http\Controllers;

use App\Person;
use Illuminate\Http\Request;
use Validator;
use App\User;
use App\DrishteeMitra;
use Auth;
use App\PersonBan;

class PersonBanController extends Controller
{
    /**
    * @param  \Illuminate\Http\Request  $request
    * @param  \App\DrishteeMitra $dm_id
    * @param  \App\Person $person_id
    * @return \App\PersonBan $pdr
    * do store request for person ban
    */
    public function personBanRequest(Request $request,$dm_id, $person_id){
    	$request['requester_id'] = $dm_id;
    	$request['person_id'] = $person_id;
		
		$pdr = PersonBan::where('requester_id',$dm_id)->where('person_id',$person_id)->first();
    	if(!$pdr){
    		$pdr = PersonBan::create($request->all());
    	}
    	return response()->json($pdr,200);
    }

    /**
    * @return \App\PersonBan $pdr
    * do get list of personban request return with person, dm, user
    */
    public function getPersonBanRequests(){
		$pdr = PersonBan::all();
    	return response()->json($pdr->load('person','dm','user'),200);
    }

    /**
    * @param  \Illuminate\Http\Request  $request
    * @return \App\PersonBan $pdr
    * do ban person 
    */
    public function approvePersonBanRequests(Request $request,$id){
    	$user = Auth::User();
    	$pdr = PersonBan::find($id);
    	$person = $pdr->person;
    	$person->status = $request['status'];
    	$person->save();
    	$pdr->status = $request['status'];
    	$pdr->approver_id = $user->id;
    	// $pdr->approver_id = 1;
    	$pdr->save();
    	return response()->json($pdr->load('person','dm','user'),200);
    }
}
