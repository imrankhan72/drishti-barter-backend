<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\DrishteeMitra;
use App\Person;
use App\PersonProduct;
use App\TejasRequestSellToPeople;
use App\TejasProductSellToPerson;
use App\Ledger;
use App\DmMarginPercentage;
use App\helpers;

class TejasRequestSellToPeopleController extends Controller
{
    /**
     * @param App\DrishteeMitra $dm_id
     * @param App\Person $person_id
     * @param Illuminate\Http\Request $request
     * @return App\TejasRequestSellToPeople $trstp, App\TejasProductSellToPerson $tpstp
     * do store selltoperson product request, create transaction history and modify person ledger, personProduct  
     * 
     */
	  public function sellToPersonRequest($dm_id, $person_id, Request $request){
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
        $collection = [];
        $dm = DrishteeMitra::find($dm_id);
        $dm_person = $dm->person;
        $dm_ledger = Ledger::find($dm->ledger_id);
        //$dm_person_ledger = Ledger::find($dm_person->ledger_id);
        $person = Person::find($person_id);
        $person_ledger = Ledger::find($person->ledger_id);
        $dm_margin = DmMarginPercentage::first();        
        $total_lp = 0;
        foreach ($formData['data'] as $data) {
            $total_lp += $data['lp_applicable'];
        }
        $dm_margin_lp = $total_lp*($dm_margin->dm_margin_percentage/100);
        if($person_ledger->balance >= $total_lp){
            $trstp['person_id'] = $person_id;
            $trstp['requester_person_id'] = $dm_person->id;
            $trstp['status'] = "Complete";
            
            $trstp = TejasRequestSellToPeople::create($trstp);
            foreach ($formData['data'] as $data) {
                $pp = PersonProduct::where('person_id',$dm_person->id)->where('product_id',$data['product_id'])->first();
                $calculat_lp = $pp->product_lp * $data['quantity'];
                if($pp->quantity_available >= $data['quantity'] && $calculat_lp == $data['lp_applicable']){
                    $pp->quantity_available = $pp->quantity_available - $data['quantity'];
                    $pp->save();

                    $personProduct = PersonProduct::where('person_id',$person_id)->where('product_id',$data['product_id'])->first();
                    if($personProduct){
                      $personProduct->quantity_available = $personProduct->quantity_available + $data['quantity'];
                      $personProduct->save();
                    }else{
                      $personProduct = new PersonProduct();
                      $personProduct->geography_id = $pp->geography_id;
                      $personProduct->geography_type = $pp->geography_type;
                      $personProduct->dm_id = $pp->dm_id;
                      $personProduct->person_id = $person_id;
                      $personProduct->product_id = $pp->product_id;
                      $personProduct->unit_id = $pp->unit_id;
                      $personProduct->quantity_available = $data['quantity'];
                      $personProduct->product_lp = $pp->product_lp;
                      $personProduct->active_on_barterplace = $pp->active_on_barterplace;
                      $personProduct->save();
                    }

                    $data['sell_request_id'] = $trstp->id;
                    $tpstp = TejasProductSellToPerson::create($data);
                    array_push($collection,$tpstp);
                    
                }else{
                    foreach ($collection as $tpstp) {
                        $tpstp = TejasProductSellToPerson::find($tpstp->id);
                        $tpstp->delete();
                    }
                    $trstp->delete();
                    return response()->json(['error'=>"Product Quantity More Than Available Product Quantity."],422);
                }
            }
        }else{
            return response()->json(['error'=>"Person Have Not Enough LP."],422);      
        }
        $trstp->createTejasSellLedgerTransactions('Success',$dm_ledger->id,'Cr',$total_lp,'SellToPerson Balance Credit',$dm_ledger->balance + $total_lp,$dm_person->id);
            
        $trstp->createTejasSellLedgerTransactions('Success',$person_ledger->id,'Dr',$total_lp,'SellToPerson Balance Debit',$person_ledger->balance - $total_lp,$person_id);
        $trstp->createTejasSellLedgerTransactions('Success',$dm_ledger->id,'Cr',$dm_margin_lp,'SellToPerson Margin Add To DM',$dm_ledger->balance + $dm_margin_lp,$dm->id);

        $dm_ledger->balance = $dm_ledger->balance + $total_lp;
        $dm_ledger->save();
        $person_ledger->balance = $person_ledger->balance - $total_lp;
        $person_ledger->save();
        $dm_ledger->balance = $dm_ledger->balance + $dm_margin_lp;
        $dm_ledger->save();
        sendSMS("You have bought Tejas products worth ".$total_lp." from Drishtee Mitra. You account balance is ".$person_ledger->balance ,$person->mobile);
        return response()->json(['selltopersonrequest'=>$trstp,"products"=>$collection],200);  
    }

    /**
     * @param App\DrishteeMitra $dm_id
     * @return App\TejasRequestSellToPeople $trstp
     * return list of TejasRequestSellToPeople related to dm_id 
     * 
     */
    public function sellToPersonRequests($dm_id){
      	$dm_person = DrishteeMitra::find($dm_id)->person; 
      	$trstp = TejasRequestSellToPeople::where('requester_person_id',$dm_person->id)->get();
      	return response()->json($trstp->load('requester_person.personPersonalDetails','person.personPersonalDetails','sellToPersonProducts.product.units'),200);
    }

     /**
     * @param App\DrishteeMitra $dm_id
     * @param App\TejasRequestSellToPeople $request_id
     * @return App\TejasRequestSellToPeople $trstp
     * return single object of TejasRequestSellToPeople related to request_id 
     * 
     */
    public function sellToPersonSingleRequest($dm_id,$request_id){
        $trstp = TejasRequestSellToPeople::find($request_id);
      	return response()->json($trstp->load('requester_person.personPersonalDetails','person.personPersonalDetails','sellToPersonProducts.product.units'),200);
    }
}
