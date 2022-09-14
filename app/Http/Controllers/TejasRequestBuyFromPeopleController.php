<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\DrishteeMitra;
use App\Person;
use App\PersonProduct;
use App\TejasRequestBuyFromPeople;
use App\TejasProductBuyFromPerson;
use App\Ledger;
use App\DmMarginPercentage;
use App\helpers;

class TejasRequestBuyFromPeopleController extends Controller
{
    /**
     * @param App\DrishteeMitra $dm_id
     * @param App\Person $person_id
     * @param Illuminate\Http\Request $request
     * @return App\TejasRequestBuyFromPeople $trbfp, App\TejasProductBuyFromPerson $tpbfp
     * do store buyfromperson product request, create transaction history and modify person ledger, personProduct  
     * 
     */
    public function buyFromPersonRequest($dm_id, $person_id, Request $request){
        $regex = "/^(?=.+)(?:[1-9]\d*|0)?(?:\.\d+)?$/";
        $formData = ['data'=>$request->all()];

        $validation = Validator::make($formData,[
            "data.*.product_id"   => "required|exists:products,id",
            "data.*.product_name" => "required|string",
            "data.*.quantity"     => array('required','regex:'.$regex),
            "data.*.unit"         => "required|string",
            "data.*.lp_applicable"=> array('required','regex:'.$regex)
        ]);

        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,400);
        }
        // return response()->json($request->all(),200);
        $collection = [];
        $dm = DrishteeMitra::find($dm_id);
        $dm_person = $dm->person;
        $dm_ledger = Ledger::find($dm->ledger_id);
        // $dm_person_ledger = Ledger::find($dm_person->ledger_id);
        $person = Person::find($person_id);
        $person_ledger = Ledger::find($person->ledger_id);
        $dm_margin = DmMarginPercentage::first();
        $total_lp = 0;
        foreach ($formData['data'] as $data) {
            $total_lp += $data['lp_applicable'];
        }
        $dm_margin_lp = $total_lp*($dm_margin->dm_margin_percentage/100);
        if($dm_ledger->balance >= $total_lp){
            $trbfp['person_id'] = $person_id;
            $trbfp['requester_person_id'] = $dm_person->id;
            $trbfp['status'] = "Complete";
            
            $trbfp = TejasRequestBuyFromPeople::create($trbfp);
            foreach ($formData['data'] as $data) {
                $pp = PersonProduct::where('person_id',$person_id)->where('product_id',$data['product_id'])->first();

                $calculat_lp = $pp->product_lp * $data['quantity'];
                if($pp->quantity_available >= $data['quantity'] && $calculat_lp == $data['lp_applicable']){
                    $pp->quantity_available = $pp->quantity_available - $data['quantity'];
                    $pp->save();

                    $personProduct = PersonProduct::where('person_id',$dm_person->id)->where('product_id',$data['product_id'])->first();
                    if($personProduct){
                      $personProduct->quantity_available = $personProduct->quantity_available + $data['quantity'];
                      $personProduct->save();
                    }else{
                      $personProduct = new PersonProduct();
                      $personProduct->geography_id = $pp->geography_id;
                      $personProduct->geography_type = $pp->geography_type;
                      $personProduct->dm_id = $pp->dm_id;
                      $personProduct->person_id = $dm_person->id;
                      $personProduct->product_id = $pp->product_id;
                      $personProduct->unit_id = $pp->unit_id;
                      $personProduct->quantity_available = $data['quantity'];
                      $personProduct->product_lp = $pp->product_lp;
                      $personProduct->active_on_barterplace = $pp->active_on_barterplace;
                      $personProduct->save();
                    }

                    $data['buy_request_id'] = $trbfp->id;
                    $tpbfp = TejasProductBuyFromPerson::create($data);
                    array_push($collection,$tpbfp);
                    
                }else{
                    foreach ($collection as $tpbfp) {
                        $tpbfp = TejasProductBuyFromPerson::find($tpbfp->id);
                        $tpbfp->delete();
                    }
                    $trbfp->delete();
                    return response()->json(['error'=>"Product Quantity More Than Available Product Quantity."],422);
                }
            }
        }else{
            return response()->json(['error'=>"Person Have Not Enough LP."],422);      
        }

        $trbfp->createTejasBuyLedgerTransactions('Success',$dm_ledger->id,'Dr',$total_lp,'BuyFromPerson Balance Debit',$dm_ledger->balance - $total_lp,$dm_person->id);
        $trbfp->createTejasBuyLedgerTransactions('Success',$person_ledger->id,'Cr',$total_lp,'BuyFromPerson Balance Credit',$person_ledger->balance + $total_lp,$person_id);
        // $trbfp->createTejasBuyLedgerTransactions('Success',$dm_ledger->id,'Cr',$dm_margin_lp,'BuyFromPerson Margin Add To DM',$dm_ledger->balance + $dm_margin_lp,$dm->id);
        $dm_ledger->balance = $dm_ledger->balance - $total_lp;
        $dm_ledger->save();
        $person_ledger->balance = $person_ledger->balance + $total_lp;
        $person_ledger->save();
        // $dm_ledger->balance = $dm_ledger->balance + $dm_margin_lp;
        // $dm_ledger->save();
        sendSMS("You have sold Tejas Products to DM. Your account has been credited ".$total_lp." LPs. You available account balance is ".$person_ledger->balance." LP.".$total_lp ,$person->mobile);
        return response()->json(['buyfrompersonrequest'=>$trbfp,"products"=>$collection],200);
    }
    /**
     * @param App\DrishteeMitra $dm_id
     * @param App\TejasRequestBuyFromPeople $request_id
     * @return App\TejasRequestBuyFromPeople $trstp
     * return list of TejasRequestBuyFromPeople related to dm_id 
     * 
     */
    public function buyFromPersonRequests($dm_id){
      	$dm_person = DrishteeMitra::find($dm_id)->person; 
      	$trbfp = TejasRequestBuyFromPeople::where('requester_person_id',$dm_person->id)->get();
      	return response()->json($trbfp->load('requester_person.personPersonalDetails','person.personPersonalDetails','buyFromPersonProducts.product.units'),200);
    }

    /**
     * @param App\DrishteeMitra $dm_id
     * @param App\TejasRequestBuyFromPeople $request_id
     * @return App\TejasRequestBuyFromPeople $trstp
     * return single object of TejasRequestBuyFromPeople related to request_id 
     * 
     */
    public function buyFromPersonSingleRequest($dm_id,$request_id){
      	$trstp = TejasRequestBuyFromPeople::find($request_id);
        return response()->json($trstp->load('requester_person.personPersonalDetails','person.personPersonalDetails','buyFromPersonProducts.product.units'),200);
    }
} 
