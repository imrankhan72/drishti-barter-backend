<?php

namespace App\Http\Controllers;


use App\BarterMatch;
use Illuminate\Http\Request;
use App\BarterMatchLocalInventoryProduct;
use App\BarterMatchLocalInventoryService;
use App\BarterMatchLocalInventoryLp;
use Validator;
use App\BarterConfirmation;
use App\Barter;
use App\Person;
use App\Ledger;
use App\DmMarginPercentage;
use App\DrishteeMitra;
use App\PersonProduct;
use Carbon\Carbon;
use App\PersonService;
use App\BarterHaveService;
use App\Service;
use Log;

class BarterMatchController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    return response()->json(BarterMatch::all(), 200);
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
  public function store(Request $request, $id)
  {
    $validation = Validator::make($request->all(), [
      'barter_id'                   => 'required|exists:barters,id',
      'match_type'                  => 'required',
      'barter_2_id'                 => 'sometimes',
      'person_id'                   => 'required|exists:people,id',
      'total_lp_offered'            => 'required',
      'local_inventory_type'        => 'required',
      'product_id'                  => 'required_if:local_inventory_type,product|exists:products,id',
      'product_lp'                  => 'required_if:local_inventory_type,product|numeric',
      'service_lp'                  => 'required_if:local_inventory_type,service|numeric',
      'service_id'                  => 'required_if:local_inventory_type,service|exists:services,id',
      'no_of_days'                  => 'required_if:local_inventory_type,service',
      'lp'                          => 'required_if:local_inventory_type,lp',
      'product_quantity'            => 'required_if:local_inventory_type,product'
    ]);
    if ($validation->fails()) {
      $errors = $validation->errors();
      return response()->json($errors, 400);
    }

    Log::info("Barter Match Controller store " . $request);
    $bm = BarterMatch::create($request->only('barter_id', 'match_type', 'barter_2_id', 'person_id', 'total_lp_offered', 'local_inventory_type'));
    $request['barter_match_id'] = $bm->id;
    if ($request['local_inventory_type'] == 'product') {
      $bmlip = BarterMatchLocalInventoryProduct::create($request->only('barter_match_id', 'barter_id', 'product_lp', 'product_id', 'product_quantity'));
    }
    if ($request['local_inventory_type']  == 'service') {
      $bmlis = BarterMatchLocalInventoryService::create($request->only('barter_match_id', 'barter_id', 'service_lp', 'service_id', 'no_of_days'));
    }
    if ($request['local_inventory_type'] == 'lp') {
      $bmlil = BarterMatchLocalInventoryLp::create($request->only('barter_id', 'barter_match_id', 'lp'));
    }

    return response()->json($bm->load('barterMatchLocalInventoryLps', 'barterMatchLocalInventoryServices', 'barterMatchLocalInventoryProducts'), 201);
  }

  /**
   * Display the specified resource.
   *
   * @param  \App\BarterMatch  $barterMatch
   * @return \Illuminate\Http\Response
   */
  public function show(BarterMatch $barterMatch)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  \App\BarterMatch  $barterMatch
   * @return \Illuminate\Http\Response
   */
  public function edit(BarterMatch $barterMatch)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \App\BarterMatch  $barterMatch
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, BarterMatch $barterMatch)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  \App\BarterMatch  $barterMatch
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $bm = BarterMatch::find($id);
    if ($bm) {
      $bmlips = BarterMatchLocalInventoryProduct::where('barter_match_id', $bm->id)->get();
      foreach ($bmlips as $bmlip) {
        $bmlip->destroy($bmlip->id);
      }
      $bmliss = BarterMatchLocalInventoryService::where('barter_match_id', $bm->id)->get();
      foreach ($bmliss as $bmlis) {
        $bmlis->destroy($bmlis->id);
      }
      $bmlils = BarterMatchLocalInventoryLp::where('barter_match_id', $bm->id)->get();
      foreach ($bmlils as $bmlil) {
        $bmlil->destroy($bmlil->id);
      }

      $bm->destroy($bm->id);
      return response()->json($bm, 201);
    }
    return response()->json(['error' => 'BarterMatch Not Found'], 404);
  }
  public function barterConfirmation(Request $request)
  {
    $validation = Validator::make($required->all(), [
      'barter_id'                 => 'required|exists:barters,id',
      'person_id'                 => 'required|exists:people,id',
      'status'                    => 'required',
      'confirmation_type'         => 'required',
      'confirmation_time'         => 'required'
    ]);
    if ($validation->fails()) {
      $errors = $validation->errors();
      return response()->json($errors, 400);
    }
    $barter_confirmation = BarterConfirmation::create($request->all());
    return response()->json($barter_confirmation, 201);
  }
  public function checkBarterMatchComplete()
  {
    $barter = Barter::find($barter_id);
    $barter->status = 'Locked';
    $barter->save();
    return response()->json($barter, 200);
  }
  public function barterMatchConfirm($barter_id)
  {

    $barter = Barter::find($barter_id);
    // $d = PersonService::all();
    // $d = BarterHaveService::find(8);
    // $d = $barter->barterHaveServices;
    // return response()->json($d->personService->service,200);


    // dd($barter);
    $barter->status = 'Locked';
    $barter->save();
    $temp['barter_id'] = $barter->id;
    $temp['person_id'] = $barter->person_id;
    $barter_confirm = BarterConfirmation::create($temp);
    foreach ($barter->barterMatches as $bm) {
      $bm['status'] = 'Open';
      BarterConfirmation::create($bm->only('person_id', 'barter_id', 'status'));
    }
    $dm = DrishteeMitra::find($barter->added_by_dm_id);
    $dmt['barter_id'] = $barter->id;
    $dmt['person_id'] = $dm->person_id;
    $dmt['status'] = 'Open';
    $url = env('APP_URL');

    BarterConfirmation::create($dmt);

    $serviceTextHave = "";
    $serviceTextNeed = "";
    $productTextHave = "";
    $productTextNeed = "";
    $barter_confirmation = BarterConfirmation::where('barter_id', $barter_id)->get();
    foreach ($barter_confirmation as $bc) {
      $c_url = $url . 'api/bartermatchconfirm/' . $bc->id . '/statuschange?status=Confirmed';
      $person = Person::find($bc->person_id);
      foreach ($barter->barterHaveServices as $bhs) {
        foreach ($barter->barterNeedServices as $bns) {

          $personHaveserv = PersonService::find($bhs->person_service_id);
          $ps = Service::find($bns->service_id);
          $serviceTextHave = $serviceTextHave . ' <Service:- ' . $personHaveserv->service->name . ' , Service LP:- ' . $bhs->service_lp . ', Hours:- ' . $bhs->no_of_days . '>';

          $serviceTextNeed = $serviceTextNeed . ' <Service:- ' . $ps->name . ' , Service LP:- ' . $bns->service_lp . ', Hours:- ' . $bns->no_of_days . '>';



          // sendSMS('Barter ID: ' . $bc->barter_id . ' You are giving <Service:- '.$bhs->personService->service->name.' , Service LP:- '.$bhs->personService->service_lp.', Hours:- '.$bhs->no_of_days.'>. You are recieving <Service:- '.$bns->personService->service->name.' , Service LP:- '.$bns->personService->service_lp.', Hours:- '.$bns->no_of_days.'>. To confirm open this link ' . $c_url . ' ', $person->mobile);
        }
      }
      foreach ($bc->barter->barterHaveProducts as $bhp) {
        foreach ($bc->barter->barterNeedProducts as $bnp) {


          $productTextHave = $productTextHave . ' <Product:- ' . $bhp->personProduct->product->name . ' , Product LP:- ' . $bhp->product_lp . ', Weight:- ' . $bhp->quantity . '>';

          $productTextNeed = $productTextNeed . ' <Product:- ' . $bnp->product->name . ' , Product LP:- ' . $bnp->product_lp . ', Weight:- ' . $bnp->quantity . '>';

          // sendSMS('Barter ID: ' . $bc->barter_id . ' You are giving <Product:- '.$bhp->personProduct->product->name.' , Product LP:- '.$bhp->product_lp.', Weight:- '.$bhp->quantity.'>. You are recieving <Product:- '.$bnp->product->name.' , Product LP:- '.$bnp->product_lp.', Weight:- '.$bnp->quantity.'>. To confirm open this link ' . $c_url . ' ', $person->mobile);
        }
      }
      // if($barter->barterHaveProducts) {
      //    dd($barter->barterHaveProducts);
      // }
      // sendSMS('Barter ID: ' . $bc->barter_id . ' You are giving '.$productTextHave.' '.$serviceTextHave.'. You are recieving '.$productTextNeed.' '.$serviceTextNeed.'. To confirm open this link ' . $c_url . ' ', $person->mobile);
    }
    return response()->json($barter, 200);
  }
  public function barterMarkComplete($barter_id)
  {
    $smsTempleteId = 1207161761463283232;
    $barter = Barter::find($barter_id);
    $barter->status = 'Completed';

    $tempLP = 0;
    $person = Person::find($barter->person_id);
    $BPledger = Ledger::find($person->ledger_id);
    $dm_margin = DmMarginPercentage::first();

    $dm = DrishteeMitra::find($barter->added_by_dm_id);
    $dmledger = Ledger::find($dm->ledger_id);

    $BMLIP = $barter->barterMatchLocalInventoryProducts;
    $BMLIS = $barter->barterMatchLocalInventoryServices;
    $BMLIL = $barter->barterMatchLocalInventoryLps;

    // Log::info("Count of match " . count($BMLIP) . " " . count($BMLIS) . " " . count($BMLIL));
    
    if (count($BMLIP) && count($BMLIL)) {
      // Log::info("BMLIP && BMLIL " . $barter->id);
      $BM = BarterMatch::orWhere([["local_inventory_type", "product"], ["barter_id", $barter->id]])
        ->orWhere([["local_inventory_type", "lp"], ["barter_id", $barter->id]])->orderBy("local_inventory_type", "ASC")->get();

      foreach ($BM as $bm) {
        $dmledgerbm = Ledger::find($dm->ledger_id);
        $bm_person = Person::find($bm->person_id);
        $ledgerbm = Ledger::find($bm_person->ledger_id);

        $bmlip = $bm->barterMatchLocalInventoryProducts;
        if ($bmlip) {
          foreach ($barter->barterHaveProducts as $bhp) {
            $pr = ($bhp->product_lp * $bhp->quantity) * ($dm_margin->dm_margin_percentage / 100);
            if ($dm_margin && $BPledger->balance >= $pr) {
              
              $barter->createBarterLedgerTransactions('Success', $dmledger->id, 'Cr', $pr, 'DM Margin Barter', $dmledger->balance + $pr, $dm->person_id);
              
              $dmledgerbal = $dmledger->balance + $pr;
              $dmledger->balance = $dmledgerbal;
              $dmledger->save();

              $BPledger->balance = $BPledger->balance - $pr;
              $BPledger->save();

              sendSMS('Barter ID: ' . $barter->id . ' is complete. Your account is Credited with ' . $pr, $dm->mobile ,$smsTempleteId);
              $pr = 0;
            }else{
              return response()->json("Bater Have Person Does Not Have Sufficient Balance To Deduct", 400); 
            }
            $pr = ($bmlip->product_lp * $bmlip->product_quantity) * ($dm_margin->dm_margin_percentage / 100);
            if ($dm_margin && $ledgerbm->balance >= $pr) {
              
              $barter->createBarterLedgerTransactions('Success', $dmledger->id, 'Cr', $pr, 'DM Margin Barter', $dmledger->balance + $pr, $dm->person_id);
              $dmledgerbal = $dmledger->balance + $pr;
              $dmledger->balance = $dmledgerbal;
              $dmledger->save();

              $ledgerbm->balance = $ledgerbm->balance - $pr;
              $ledgerbm->save();
              sendSMS('Barter ID: ' . $barter->id . ' is complete. Your account is Credited with ' . $pr, $dm->mobile,$smsTempleteId);
              $pr = 0;
            }else{
              return response()->json("Bater Match Person Does Not Have Sufficient Balance To Deduct", 400); 
            }

            $BHPP = PersonProduct::find($bhp->person_product_id);
            $BHPP->quantity_available = $BHPP->quantity_available - $bhp->quantity;
            $BHPP->save();

            $barter->createBarterLedgerTransactions('Success', $BPledger->id, 'Dr', ($bhp->product_lp * $bhp->quantity), 'Barter ID: '.$barter->id.' Sell Product '.$BHPP->product->name.' With Quantity '.$bhp->quantity.' To '.$bmlip->barterMatch->person->first_name.' '.$bmlip->barterMatch->person->last_name, $BPledger->balance - ($bhp->product_lp * $bhp->quantity), $barter->person_id);

            $barter->createBarterLedgerTransactions('Success', $ledgerbm->id, 'Cr', ($bhp->quantity * $bhp->product_lp), 'Barter ID: '.$barter->id.' Buy Product '.$BHPP->product->name.' With Quantity '.$bhp->quantity.' From '.$person->first_name.' '.$person->last_name, $ledgerbm->balance + ($bhp->quantity * $bhp->product_lp), $bm->person_id);

            $mpp = PersonProduct::where("product_id", $BHPP->product_id)->where("person_id", $bm->person_id)->first();
            if ($mpp) {
              $mpp->quantity_available = $mpp->quantity_available + $bhp->quantity;
              $mpp->save();
            } else {
              $pp = new PersonProduct();
              $pp->geography_id = $BHPP->geography_id;
              $pp->geography_type = $BHPP->geography_type;
              $pp->dm_id = $BHPP->dm_id;
              $pp->person_id = $bm->person_id;
              $pp->product_id = $BHPP->product_id;
              $pp->unit_id = $BHPP->unit_id;
              $pp->quantity_available = $bhp->quantity;
              $pp->product_lp = $BHPP->product_lp;
              $pp->active_on_barterplace = $BHPP->active_on_barterplace;
              $pp->save();
            }

            $BMLIPP = PersonProduct::where("product_id", $bmlip->product_id)->where("person_id", $bm->person_id)->first();
            $BMLIPP->quantity_available = $BMLIPP->quantity_available - $bmlip->product_quantity;
            $BMLIPP->save();

            
            $barter->createBarterLedgerTransactions('Success', $BPledger->id, 'Cr', ($bmlip->product_lp * $bmlip->product_quantity), 'Barter ID: '.$barter->id.' Buy Product '.$BMLIPP->product->name.' With Quantity '.$bmlip->product_quantity.' From '.$BMLIPP->person->first_name.' '.$BMLIPP->person->last_name, $BPledger->balance + ($bmlip->product_lp * $bmlip->product_quantity), $barter->person_id);

            $barter->createBarterLedgerTransactions('Success', $ledgerbm->id, 'Dr', ($bmlip->product_quantity * $bmlip->product_lp), 'Barter ID: '.$barter->id.' Sell Product '.$BMLIPP->product->name.' With Quantity '.$bmlip->product_quantity.' To '.$person->first_name.' '.$person->last_name, $ledgerbm->balance - ($bmlip->product_quantity * $bmlip->product_lp), $bm->person_id);

            $mpp = PersonProduct::where("product_id", $bmlip->product_id)->where("person_id", $barter->person_id)->first();
            if ($mpp) {
              $mpp->quantity_available = $mpp->quantity_available + $bmlip->product_quantity;
              $mpp->save();
            } else {
              $pp = new PersonProduct();
              $pp->geography_id = $BMLIPP->geography_id;
              $pp->geography_type = $BMLIPP->geography_type;
              $pp->dm_id = $BMLIPP->dm_id;
              $pp->person_id = $barter->person_id;
              $pp->product_id = $BMLIPP->product_id;
              $pp->unit_id = $BMLIPP->unit_id;
              $pp->quantity_available = $bmlip->product_quantity;
              $pp->product_lp = $BMLIPP->product_lp;
              $pp->active_on_barterplace = $BMLIPP->active_on_barterplace;
              $pp->save();
            }
          }

          foreach ($barter->barterHaveServices as $bhs) {
            $pr = ($bmlip->product_lp * $bmlip->product_quantity) * ($dm_margin->dm_margin_percentage / 100);
            if ($dm_margin && $ledgerbm->balance >= $pr) {
              
              $barter->createBarterLedgerTransactions('Success', $dmledger->id, 'Cr', $pr, 'DM Margin Barter', $dmledger->balance + $pr, $dm->person_id);
              $dmledgerbal = $dmledger->balance + $pr;
              
              $dmledger->balance = $dmledgerbal;
              $dmledger->save();

              $ledgerbm->balance = $ledgerbm->balance - $pr;
              $ledgerbm->save();
              
              sendSMS('Barter ID: ' . $barter->id . ' is complete. Your account is Credited with ' . $pr, $dm->mobile,$smsTempleteId);
              $pr = 0;
            }else{
              return response()->json("Bater Match Person Does Not Have Sufficient Balance To Deduct", 400); 
            }
            $pr = ($bhs->service_lp * $bhs->no_of_days) * ($dm_margin->dm_margin_percentage / 100);
            if ($dm_margin && $BPledger->balance >= $pr) {
              
              $barter->createBarterLedgerTransactions('Success', $dmledger->id, 'Cr', $pr, 'DM Margin Barter', $dmledger->balance + $pr, $dm->person_id);
              $dmledgerbal = $dmledger->balance + $pr;
              $dmledger->balance = $dmledgerbal;
              $dmledger->save();

              $BPledger->balance = $BPledger->balance - $pr;
              $BPledger->save();

              sendSMS('Barter ID: ' . $barter->id . ' is complete. Your account is Credited with ' . $pr, $dm->mobile,$smsTempleteId);
              $pr = 0;
            }else{
              return response()->json("Bater Have Person Does Not Have Sufficient Balance To Deduct", 400); 
            }

            $barter->createBarterLedgerTransactions('Success', $ledgerbm->id, 'Cr', ($bhs->service_lp * $bhs->no_of_days), 'Barter ID: '.$barter->id.' Buy Service '.$bhs->personService->service->name.' With No. Of Days: '.$bhs->no_of_days.' From '.$person->first_name.' '.$person->last_name, $BPledger->balance + ($bhs->service_lp * $bhs->no_of_days), $bm->person_id);

            $barter->createBarterLedgerTransactions('Success', $BPledger->id, 'Dr', ($bhs->no_of_days * $bhs->service_lp), 'Barter ID: '.$barter->id.' Sell Service '.$bhs->personService->service->name.' With No. Of Days '.$bhs->no_of_days.' To '.$bmlip->barterMatch->person->first_name.' '.$bmlip->barterMatch->person->last_name, $ledgerbm->balance - ($bhs->no_of_days * $bhs->service_lp), $barter->person_id);


            $BMLIPP = PersonProduct::where("product_id", $bmlip->product_id)->where("person_id", $bm->person_id)->first();
            $BMLIPP->quantity_available = $BMLIPP->quantity_available - $bmlip->product_quantity;
            $BMLIPP->save();

            $barter->createBarterLedgerTransactions('Success', $BPledger->id, 'Cr', ($bmlip->product_lp * $bmlip->product_quantity), 'Barter ID: '.$barter->id.' Buy Product '.$bmlip->product->name.' With Quantity '.$bmlip->product_quantity.' From '.$bmlip->barterMatch->person->first_name.' '.$bmlip->barterMatch->person->last_name, $ledgerbm->balance + ($bmlip->product_lp * $bmlip->product_quantity), $barter->person_id);//$bm->person_id
            

            $mpp = PersonProduct::where("product_id", $bmlip->product_id)->where("person_id", $barter->person_id)->first();
            if ($mpp) {
              $mpp->quantity_available = $mpp->quantity_available + $bmlip->product_quantity;
              $mpp->save();
            } else {
              $pp = new PersonProduct();
              $pp->geography_id = $BMLIPP->geography_id;
              $pp->geography_type = $BMLIPP->geography_type;
              $pp->dm_id = $BMLIPP->dm_id;
              $pp->person_id = $barter->person_id;
              $pp->product_id = $BMLIPP->product_id;
              $pp->unit_id = $BMLIPP->unit_id;
              $pp->quantity_available = $bmlip->product_quantity;
              $pp->product_lp = $BMLIPP->product_lp;
              $pp->active_on_barterplace = $BMLIPP->active_on_barterplace;
              $pp->save();
            }
            $barter->createBarterLedgerTransactions('Success', $ledgerbm->id, 'Dr', ($bmlip->product_quantity * $bmlip->product_lp), 'Barter ID: '.$barter->id.' Sell Product '.$bmlip->product->name.' With Quantity '.$bmlip->product_quantity.' To '.$person->first_name.' '.$person->last_name ,$BPledger->balance - ($bmlip->product_quantity * $bmlip->product_lp), $bm->person_id);
          }

          foreach ($barter->barterHaveLp as $bhl) {
            $pr = ($bmlip->product_lp * $bmlip->product_quantity) * ($dm_margin->dm_margin_percentage / 100);
            if ($dm_margin && $ledgerbm->balance >= $pr) {
              
              $barter->createBarterLedgerTransactions('Success', $dmledger->id, 'Cr', $pr, 'DM Margin Barter', $dmledger->balance + $pr, $dm->person_id);
              $dmledgerbal = $dmledger->balance + $pr;
            
              $dmledger->balance = $dmledgerbal;
              $dmledger->save();

              $ledgerbm->balance = $ledgerbm->balance - $pr;
              $ledgerbm->save();
            

              sendSMS('Barter ID: ' . $barter->id . ' is complete. Your account is Credited with ' . $pr, $dm->mobile,$smsTempleteId);
              $pr = 0;
            }else{
              return response()->json("Bater Match Person Does Not Have Sufficient Balance To Deduct", 400); 
            }
            $barter->createBarterLedgerTransactions('Success', $ledgerbm->id, 'Cr', $bhl->lp, 'Barter ID: '.$barter->id.' Buy LP: '.$bhl->lp.' From '.$person->first_name.' '.$person->last_name, $ledgerbm->balance + $bhl->lp, $bm->person_id);
            $ledgerbm->balance = $ledgerbm->balance + $bhl->lp;
            $ledgerbm->save();

            $barter->createBarterLedgerTransactions('Success', $BPledger->id, 'Dr', $bhl->lp, 'Barter ID:'.$barter->id.' Sell LP '.$bhl->lp.' To '.$bmlip->barterMatch->person->first_name.' '.$bmlip->barterMatch->person->last_name, $BPledger->balance - $bhl->lp, $barter->person_id);
            $BPledger->balance = $BPledger->balance - $bhl->lp;
            $BPledger->save();



            $BMLIPP = PersonProduct::where("product_id", $bmlip->product_id)->where("person_id", $bm->person_id)->first();
            $BMLIPP->quantity_available = $BMLIPP->quantity_available - $bmlip->product_quantity;
            $BMLIPP->save();

            $barter->createBarterLedgerTransactions('Success', $BPledger->id, 'Cr', ($bmlip->product_lp * $bmlip->product_quantity), 'Barter ID: '.$barter->id.' Buy Product '.$bmlip->product->name.' With Quantity '.$bmlip->product_quantity.' From '.$bmlip->barterMatch->person->first_name.' '.$bmlip->barterMatch->person->last_name, $ledgerbm->balance  + ($bmlip->product_lp * $bmlip->product_quantity), $barter->person_id);
            

            $mpp = PersonProduct::where("product_id", $bmlip->product_id)->where("person_id", $barter->person_id)->first();
            if ($mpp) {
              $mpp->quantity_available = $mpp->quantity_available + $bmlip->product_quantity;
              $mpp->save();
            } else {
              $pp = new PersonProduct();
              $pp->geography_id = $BMLIPP->geography_id;
              $pp->geography_type = $BMLIPP->geography_type;
              $pp->dm_id = $BMLIPP->dm_id;
              $pp->person_id = $barter->person_id;
              $pp->product_id = $BMLIPP->product_id;
              $pp->unit_id = $BMLIPP->unit_id;
              $pp->quantity_available = $bmlip->product_quantity;
              $pp->product_lp = $BMLIPP->product_lp;
              $pp->active_on_barterplace = $BMLIPP->active_on_barterplace;
              $pp->save();
            }
            $barter->createBarterLedgerTransactions('Success', $ledgerbm->id, 'Dr', ($bmlip->product_quantity * $bmlip->product_lp), 'Barter ID: '.$barter->id.' Sell Product '.$bmlip->product->name.' With Quantity '. $bmlip->product_quantity.' To '.$person->first_name.' '.$person->last_name, $BPledger->balance - ($bmlip->product_quantity * $bmlip->product_lp), $bm->person_id);            
          }
        }
        $bmlilp = $bm->barterMatchLocalInventoryLps;
        if ($bmlilp) {
          foreach ($barter->barterHaveProducts as $bhp) {
            if (($ledgerbm->balance - $bmlilp->lp) >= $tempLP) {

              $barter->createBarterLedgerTransactions('Success', $ledgerbm->id, 'Dr', $bmlilp->lp, 'Barter ID: '.$barter->id.' Sell Lp '.$bmlilp->lp.' To '.$person->first_name.' '.$person->last_name, $ledgerbm->balance - $bmlilp->lp, $bm->person_id);

              $ledgerbm->balance = $ledgerbm->balance - $bmlilp->lp;
              $ledgerbm->save();

              $barter->createBarterLedgerTransactions('Success', $BPledger->id, 'Cr', $bmlilp->lp, 'Barter ID: '.$barter->id.' Buy LP '.$bmlilp->lp.' From '.$bmlilp->barterMatch->person->first_name.' '.$bmlilp->barterMatch->person->last_name, $BPledger->balance + $bmlilp->lp, $barter->person_id);
              
              $BPledger->balance = $BPledger->balance + $bmlilp->lp;
              $BPledger->save();
            } else {
              return response()->json("Match Person Have Not Enough LP.", 400);
            }
          }

          foreach ($barter->barterHaveServices as $bhs) {

            if (($ledgerbm->balance - $bmlilp->lp) >= $tempLP) {

              $barter->createBarterLedgerTransactions('Success', $ledgerbm->id, 'Dr', $bmlilp->lp, 'Barter ID: '.$barter->id.' Sell LP '.$bmlilp->lp.' To '.$person->first_name.' '.$person->last_name, $ledgerbm->balance - $bmlilp->lp, $bm->person_id);

              $ledgerbm->balance = $ledgerbm->balance - $bmlilp->lp;
              $ledgerbm->save();

              $barter->createBarterLedgerTransactions('Success', $BPledger->id, 'Cr', $bmlilp->lp, 'Barter ID: '.$barter->id .' Buy LP '.$bmlilp->lp.' From '.$bmlilp->barterMatch->person->first_name.' '.$bmlilp->barterMatch->person->last_name, $BPledger->balance + $bmlilp->lp, $barter->person_id);

              $BPledger->balance = $BPledger->balance + $bmlilp->lp;
              $BPledger->save();

            } else {
              return response()->json("Match Person Have Not Enough LP.", 400);
            }
          }

          foreach ($barter->barterHaveLp as $bhl) {
            if ((($ledgerbm->balance - $bmlilp->lp) >= $tempLP) && (($BPledger->balance - $bhl->lp) >= $tempLP)) {

              $barter->createBarterLedgerTransactions('Success', $ledgerbm->id, 'Dr', $bmlilp->lp, 'Barter ID:'.$barter->id.' Sell LP '.$bmlilp->lp.' To'.$person->first_name.' '.$person->last_name, $ledgerbm->balance - $bmlilp->lp, $bm->person_id);
              
              $ledgerbm->balance = $ledgerbm->balance - $bmlilp->lp;
              $ledgerbm->save();

              $barter->createBarterLedgerTransactions('Success', $BPledger->id, 'Cr', $bmlilp->lp, 'Barter ID:'.$barter->id.' Buy LP '.$bmlilp->lp.' From '.$bmlilp->barterMatch->person->first_name.' '.$bmlilp->barterMatch->person->last_name , $BPledger->balance + $bmlilp->lp, $barter->person_id);
              
              $BPledger->balance = $BPledger->balance + $bmlilp->lp;
              $BPledger->save();

            } else {
              return response()->json("Barter Person Or Match Person Have Not Enough LP.", 400);
            }
          }
        }
      }
    } else if (count($BMLIS) && count($BMLIL)) {
      // Log::info("BMLIS && BMLIL " . $barter->id);
      $BM = BarterMatch::orWhere([["local_inventory_type", "service"], ["barter_id", $barter->id]])
        ->orWhere([["local_inventory_type", "lp"], ["barter_id", $barter->id]])->orderBy("local_inventory_type", "ASC")->get();

      foreach ($BM as $bm) {
        $dmledgerbm = Ledger::find($dm->ledger_id);
        $bm_person = Person::find($bm->person_id);
        $ledgerbm = Ledger::find($bm_person->ledger_id);

        $bmlis = $bm->barterMatchLocalInventoryServices;
        if ($bmlis) {
          foreach ($barter->barterHaveProducts as $bhp) {
            $pr = ($bhp->product_lp * $bhp->quantity) * ($dm_margin->dm_margin_percentage / 100);
            if ($dm_margin && $BPledger->balance >= $pr) {
              
              $barter->createBarterLedgerTransactions('Success', $dmledger->id, 'Cr', $pr, 'DM Margin Barter', $dmledger->balance + $pr, $dm->person_id);
              $dmledgerbal = $dmledger->balance + $pr;
              $dmledger->balance = $dmledgerbal;
              $dmledger->save();

              $BPledger->balance = $BPledger->balance - $pr;
              $BPledger->save();

              sendSMS('Barter ID: ' . $barter->id . ' is complete. Your account is Credited with ' . $pr, $dm->mobile,$smsTempleteId);
              $pr = 0;
            }else{
              return response()->json("Bater Have Person Does Not Have Sufficient Balance To Deduct", 400); 
            }

            $pr = ($bmlis->no_of_days * $bmlis->service_lp) * ($dm_margin->dm_margin_percentage / 100);
            if ($dm_margin && $ledgerbm->balance >= $pr) {
              
              $barter->createBarterLedgerTransactions('Success', $dmledger->id, 'Cr', $pr, 'DM Margin Barter', $dmledger->balance + $pr, $dm->person_id);
              $dmledgerbal = $dmledger->balance + $pr;
              $dmledger->balance = $dmledgerbal;
              $dmledger->save();

              $ledgerbm->balance = $ledgerbm->balance - $pr;
              $ledgerbm->save();

              sendSMS('Barter ID: ' . $barter->id . ' is complete. Your account is Credited with ' . $pr, $dm->mobile,$smsTempleteId);
              $pr = 0;
            }else{
              return response()->json("Bater Match Person Does Not Have Sufficient Balance To Deduct", 400); 
            }

            $BHPP = PersonProduct::find($bhp->person_product_id);
            $BHPP->quantity_available = $BHPP->quantity_available - $bhp->quantity;
            $BHPP->save();

            $barter->createBarterLedgerTransactions('Success', $BPledger->id, 'Dr', ($bhp->product_lp * $bhp->quantity), 'Barter ID: '.$barter->id.' Sell Product '.$bhp->personProduct->product->name.' With Quantity '.$bhp->quantity.' To '.$bmlis->barterMatch->person->first_name.' '.$bmlis->barterMatch->person->last_name, $BPledger->balance - ($bhp->product_lp * $bhp->quantity), $barter->person_id);

            $mpp = PersonProduct::where("product_id", $BHPP->product_id)->where("person_id", $bm->person_id)->first();
            if ($mpp) {
              $mpp->quantity_available = $mpp->quantity_available + $bhp->quantity;
              $mpp->save();
            } else {
              $pp = new PersonProduct();
              $pp->geography_id = $BHPP->geography_id;
              $pp->geography_type = $BHPP->geography_type;
              $pp->dm_id = $BHPP->dm_id;
              $pp->person_id = $bm->person_id;
              $pp->product_id = $BHPP->product_id;
              $pp->unit_id = $BHPP->unit_id;
              $pp->quantity_available = $bhp->quantity;
              $pp->product_lp = $BHPP->product_lp;
              $pp->active_on_barterplace = $BHPP->active_on_barterplace;
              $pp->save();
            }
            $barter->createBarterLedgerTransactions('Success', $ledgerbm->id, 'Cr', ($bhp->quantity * $bhp->product_lp), 'Barter ID: '.$barter->id.' Buy Product '.$bhp->personProduct->product->name.' With Quantity: '.$bhp->quantity.' From '.$person->first_name.' '.$person->last_name, $ledgerbm->balance - ($bhp->quantity * $bhp->product_lp), $bm->person_id);
           

            $barter->createBarterLedgerTransactions('Success', $BPledger->id, 'Cr', ($bmlis->no_of_days * $bmlis->service_lp), 'Barter ID: '.$barter->id.' Buy Service '.$bmlis->service->name.' With No. Of Days '.$bmlis->no_of_days.' From '.$bm_person->first_name.' '.$bm_person->last_name, $BPledger->balance + ($bmlis->no_of_days * $bmlis->service_lp), $person->id);

            $barter->createBarterLedgerTransactions('Success', $ledgerbm->id, 'Dr', ($bmlis->no_of_days * $bmlis->service_lp), 'Barter ID: '.$barter->id.' Sell Service '.$bmlis->service->name.' With No. Of Days '.$bmlis->no_of_days.' To '.$person->first_name.' '.$person->last_name, $ledgerbm->balance - ($bmlis->no_of_days * $bmlis->service_lp), $bm->person_id);
          }

          foreach ($barter->barterHaveServices as $bhs) {
            $pr = ($bhs->service_lp * $bhs->no_of_days) * ($dm_margin->dm_margin_percentage / 100);
            if ($dm_margin && $BPledger->balance >= $pr) {
              
              $barter->createBarterLedgerTransactions('Success', $dmledger->id, 'Cr', $pr, 'DM Margin Barter', $dmledger->balance + $pr, $dm->person_id);
              $dmledgerbal = $dmledger->balance + $pr;
              $dmledger->balance = $dmledgerbal;
              $dmledger->save();

              $BPledger->balance = $BPledger->balance - $pr;
              $BPledger->save();

              sendSMS('Barter ID: ' . $barter->id . ' is complete. Your account is Credited with ' . $pr, $dm->mobile,$smsTempleteId);
              $pr = 0;
            }else{
              return response()->json("Bater Have Person Does Not Have Sufficient Balance To Deduct", 400); 
            }
            $pr = ($bmlis->no_of_days * $bmlis->service_lp) * ($dm_margin->dm_margin_percentage / 100);
            if ($dm_margin && $ledgerbm->balance >= $pr) {
              
              $barter->createBarterLedgerTransactions('Success', $dmledger->id, 'Cr', $pr, 'DM Margin Barter', $dmledger->balance + $pr, $dm->person_id);
              $dmledgerbal = $dmledger->balance + $pr;
              $dmledger->balance = $dmledgerbal;
              $dmledger->save();

              $ledgerbm->balance = $ledgerbm->balance - $pr;
              $ledgerbm->save();

              sendSMS('Barter ID: ' . $barter->id . ' is complete. Your account is Credited with ' . $pr, $dm->mobile,$smsTempleteId);
              $pr = 0;
            }else{
              return response()->json("Bater Match Person Does Not Have Sufficient Balance To Deduct", 400); 
            }
            $barter->createBarterLedgerTransactions('Success', $ledgerbm->id, 'Cr', ($bhs->service_lp * $bhs->no_of_days), 'Barter ID: '.$barter->id.' Buy Service '.$bhs->personService->service->name.' With No Of Days '.$bhs->no_of_days.' From '.$person->first_name.' '.$person->last_name, $ledgerbm->balance + ($bhs->service_lp * $bhs->no_of_days), $bm->person_id);

            $barter->createBarterLedgerTransactions('Success', $BPledger->id, 'Dr', ($bhs->no_of_days * $bhs->service_lp), 'Barter ID: '.$barter->id.' Sell Service '.$bhs->personService->service->name .' With No Of Days '.$bhs->no_of_days.' To '.$bm_person->first_name.' '.$bm_person->last_name, $BPledger->balance - ($bhs->no_of_days * $bhs->service_lp), $barter->person_id);

            $barter->createBarterLedgerTransactions('Success', $ledgerbm->id, 'Dr', ($bmlis->no_of_days * $bmlis->service_lp), 'Barter ID: '.$barter->id.' Sell Service '.$bmlis->service->name.' With No Of Days'.$bmlis->no_of_days.' To '. $person->first_name.' '.$person->last_name, $ledgerbm->balance - ($bmlis->no_of_days * $bmlis->service_lp), $bm->person_id);

            $barter->createBarterLedgerTransactions('Success', $BPledger->id, 'Cr', ($bmlis->no_of_days * $bmlis->service_lp), 'Barter ID: '.$barter->id.' Buy Service '.$bmlis->service->name.' With No Of Days'.$bmlis->no_of_days.' From '.$bmlis->barterMatch->person->first_name.' '.$bmlis->barterMatch->person->last_name, $BPledger->balance + ($bmlis->no_of_days * $bmlis->service_lp), $barter->person_id);
          }
          
          foreach ($barter->barterHaveLp as $bhl) {

            if (($BPledger->balance - $bhl->lp) >= $tempLP) {

              $barter->createBarterLedgerTransactions('Success', $ledgerbm->id, 'Cr', $bhl->lp, 'Barter ID: '.$barter->id.' Buy LP '.$bhl->lp.' From '.$person->first_name.' '.$person->last_name, $ledgerbm->balance + $bhl->lp, $bm->person_id);
              $ledgerbm->balance = $ledgerbm->balance + $bhl->lp;
              $ledgerbm->save();

              $barter->createBarterLedgerTransactions('Success', $BPledger->id, 'Dr', $bhl->lp, 'Barter ID: '.$barter->id.' Sell LP '. $bhl->lp . ' To'.$bmlis->barterMatch->person->first_name.' '.$bmlis->barterMatch->person->last_name, $BPledger->balance - $bhl->lp, $barter->person_id);
              $BPledger->balance = $BPledger->balance - $bhl->lp;
              $BPledger->save();

              $barter->createBarterLedgerTransactions('Success', $BPledger->id, 'Cr', ($bmlis->no_of_days * $bmlis->service_lp), 'Barter ID: '.$barter->id.' Buy Service '.$bmlis->service->name.' With No Of Days'.$bmlis->no_of_days.' From '.$bmlis->barterMatch->person->first_name.' '.$bmlis->barterMatch->person->last_name, $ledgerbm->balance + ($bmlis->no_of_days * $bmlis->service_lp), $barter->person_id);
              

              $barter->createBarterLedgerTransactions('Success', $ledgerbm->id, 'Dr', ($bmlis->no_of_days * $bmlis->service_lp), 'Barter ID: '.$barter->id.' Sell Service '.$bmlis->service->name.' With No Of Days '.$bmlis->no_of_days.' To '.$person->first_name.' '.$person->last_name, $BPledger->balance - ($bmlis->no_of_days * $bmlis->service_lp), $bm->person_id);

              if ($dm_margin) {
                $pr = ($bmlis->no_of_days * $bmlis->service_lp) * ($dm_margin->dm_margin_percentage / 100);
                $barter->createBarterLedgerTransactions('Success', $dmledger->id, 'Cr', $pr, 'DM Margin Barter', $dmledger->balance + $pr, $dm->person_id);
                $dmledgerbal = $dmledger->balance + $pr;
                $dmledger->balance = $dmledgerbal;
                $dmledger->save();

                $ledgerbm->balance = $ledgerbm->balance - $pr;
                $ledgerbm->save();

                sendSMS('Barter ID: ' . $barter->id . ' is complete. Your account is Credited with ' . $pr, $dm->mobile,$smsTempleteId);
                $pr = 0;
              }
            } else {
              return response()->json("Barter Person Have Not Enough LP.", 400);
            }
          }
        }
        $bmlilp = $bm->barterMatchLocalInventoryLps;
        if ($bmlilp) {
          foreach ($barter->barterHaveProducts as $bhp) {
            if (($ledgerbm->balance - $bmlilp->lp) >= $tempLP) {

              $barter->createBarterLedgerTransactions('Success', $ledgerbm->id, 'Dr', $bmlilp->lp, 'Barter ID: '.$barter->id.' Sell Lp '.$bmlilp->lp.' To '.$person->first_name.' '.$person->last_name, $ledgerbm->balance - $bmlilp->lp, $bm->person_id);

              $ledgerbm->balance = $ledgerbm->balance - $bmlilp->lp;
              $ledgerbm->save();

              $barter->createBarterLedgerTransactions('Success', $BPledger->id, 'Cr', $bmlilp->lp, 'Barter ID: '.$barter->id.' Buy LP '.$bmlilp->lp.' From '.$bmlilp->barterMatch->person->first_name.' '.$bmlilp->barterMatch->person->last_name, $BPledger->balance + $bmlilp->lp, $barter->person_id);
              
              $BPledger->balance = $BPledger->balance + $bmlilp->lp;
              $BPledger->save();

            } else {
              return response()->json("Match Person Have Not Enough LP.", 400);
            }
          }

          foreach ($barter->barterHaveServices as $bhs) {

            if (($ledgerbm->balance - $bmlilp->lp) >= $tempLP) {

              $barter->createBarterLedgerTransactions('Success', $ledgerbm->id, 'Dr', $bmlilp->lp, 'Barter ID: '.$barter->id.' Sell LP '.$bmlilp->lp.' To '.$person->first_name.' '.$person->last_name, $ledgerbm->balance - $bmlilp->lp, $bm->person_id);

              $ledgerbm->balance = $ledgerbm->balance - $bmlilp->lp;
              $ledgerbm->save();

              $barter->createBarterLedgerTransactions('Success', $BPledger->id, 'Cr', $bmlilp->lp, 'Barter ID: '.$barter->id .' Buy LP '.$bmlilp->lp.' From '.$bmlilp->barterMatch->person->first_name.' '.$bmlilp->barterMatch->person->last_name, $BPledger->balance + $bmlilp->lp, $barter->person_id);

              $BPledger->balance = $BPledger->balance + $bmlilp->lp;
              $BPledger->save();

            } else {
              return response()->json("Match Person Have Not Enough LP.", 400);
            }
          }

          foreach ($barter->barterHaveLp as $bhl) {
            if ((($ledgerbm->balance - $bmlilp->lp) >= $tempLP) && (($BPledger->balance - $bhl->lp) >= $tempLP)) {

              $barter->createBarterLedgerTransactions('Success', $ledgerbm->id, 'Dr', $bmlilp->lp, 'Barter ID:'.$barter->id.' Sell LP '.$bmlilp->lp.' To'.$person->first_name.' '.$person->last_name, $ledgerbm->balance - $bmlilp->lp, $bm->person_id);
              
              $ledgerbm->balance = $ledgerbm->balance - $bmlilp->lp;
              $ledgerbm->save();

              $barter->createBarterLedgerTransactions('Success', $BPledger->id, 'Cr', $bmlilp->lp, 'Barter ID:'.$barter->id.' Buy LP '.$bmlilp->lp.' From '.$bmlilp->barterMatch->person->first_name.' '.$bmlilp->barterMatch->person->last_name , $BPledger->balance + $bmlilp->lp, $barter->person_id);
              
              $BPledger->balance = $BPledger->balance + $bmlilp->lp;
              $BPledger->save();

            } else {
              return response()->json("Barter Person Or Match Person Have Not Enough LP.", 400);
            }
          }
        }
      }
    } else if (count($BMLIP)) {
      // Log::info("BMLIP " . $barter->id);
      $BM = BarterMatch::orWhere([["local_inventory_type", "product"], ["barter_id", $barter->id]])->get();

      foreach ($BM as $bm) {
        $dmledgerbm = Ledger::find($dm->ledger_id);
        $bm_person = Person::find($bm->person_id);
        $ledgerbm = Ledger::find($bm_person->ledger_id);

        $bmlip = $bm->barterMatchLocalInventoryProducts;
        if ($bmlip) {

          foreach ($barter->barterHaveProducts as $bhp) {

            $BHPP = PersonProduct::find($bhp->person_product_id);
            
            $pr = ($bhp->product_lp * $bhp->quantity) * ($dm_margin->dm_margin_percentage / 100);
            if ($dm_margin && $BPledger->balance >= $pr ) {
              
              $barter->createBarterLedgerTransactions('Success', $dmledger->id, 'Cr', $pr, 'DM Margin Barter', $dmledger->balance + $pr, $dm->person_id);
              $dmledgerbal = $dmledger->balance + $pr;
              $dmledger->balance = $dmledgerbal;
              $dmledger->save();

              $BPledger->balance = $BPledger->balance - $pr;
              $BPledger->save();

              sendSMS('Barter ID: ' . $barter->id . ' is complete. Your account is Credited with ' . $pr, $dm->mobile,$smsTempleteId);
              $pr = 0;
            }else{
              return response()->json("Bater Have Person Does Not Have Sufficient Balance To Deduct", 400); 
            }

            $pr = ($bmlip->product_lp * $bmlip->product_quantity) * ($dm_margin->dm_margin_percentage / 100);
            if ($dm_margin && $ledgerbm->balance >= $pr) {
              
              $barter->createBarterLedgerTransactions('Success', $dmledger->id, 'Cr', $pr, 'DM Margin Barter', $dmledger->balance + $pr, $dm->person_id);
              $dmledgerbal = $dmledger->balance + $pr;
              $dmledger->balance = $dmledgerbal;
              $dmledger->save();

              $ledgerbm->balance = $ledgerbm->balance - $pr;
              $ledgerbm->save();

              sendSMS('Barter ID: ' . $barter->id . ' is complete. Your account is Credited with ' . $pr, $dm->mobile,$smsTempleteId);
              $pr = 0;
            }else{
              return response()->json("Bater Match Person Does Not Have Sufficient Balance To Deduct", 400); 
            }


            $BHPP->quantity_available = $BHPP->quantity_available - $bhp->quantity;
            $BHPP->save();

            $barter->createBarterLedgerTransactions('Success', $BPledger->id, 'Dr', ($bhp->product_lp * $bhp->quantity), 'Barter ID: '.$barter->id.' Sell Product '.$bhp->personProduct->product->name.' With Quantity '.$bhp->quantity.' To '.$bmlip->barterMatch->person->first_name.' '.$bmlip->barterMatch->person->last_name, $BPledger->balance - ($bhp->product_lp * $bhp->quantity), $barter->person_id);

            

            $mpp = PersonProduct::where("product_id", $BHPP->product_id)->where("person_id", $bm->person_id)->first();
            if ($mpp) {
              $mpp->quantity_available = $mpp->quantity_available + $bhp->quantity;
              $mpp->save();
            } else {
              $pp = new PersonProduct();
              $pp->geography_id = $BHPP->geography_id;
              $pp->geography_type = $BHPP->geography_type;
              $pp->dm_id = $BHPP->dm_id;
              $pp->person_id = $bm->person_id;
              $pp->product_id = $BHPP->product_id;
              $pp->unit_id = $BHPP->unit_id;
              $pp->quantity_available = $bhp->quantity;
              $pp->product_lp = $BHPP->product_lp;
              $pp->active_on_barterplace = $BHPP->active_on_barterplace;
              $pp->save();
            }
            
            $barter->createBarterLedgerTransactions('Success', $ledgerbm->id, 'Cr', ($bhp->quantity * $bhp->product_lp), 'Barter ID: '.$barter->id.' Buy Product '.$bhp->personProduct->product->name.' With Quantity '.$bhp->quantity.' From '.$person->first_name.' '.$person->last_name, $ledgerbm->balance + ($bhp->quantity * $bhp->product_lp), $bm->person_id);
            


            $BMLIPP = PersonProduct::where("product_id", $bmlip->product_id)->where("person_id", $bm->person_id)->first();
            $BMLIPP->quantity_available = $BMLIPP->quantity_available - $bmlip->product_quantity;
            $BMLIPP->save();

            $barter->createBarterLedgerTransactions('Success', $ledgerbm->id, 'Dr', ($bmlip->product_lp * $bmlip->product_quantity), 'Barter ID: '.$barter->id.' Sell Product '.$bmlip->product->name.' With Quantity '.$bmlip->product_quantity.' To '.$person->first_name.' '.$person->last_name, $ledgerbm->balance - ($bmlip->product_lp * $bmlip->product_quantity), $bm->person_id);
            

            $mpp = PersonProduct::where("product_id", $bmlip->product_id)->where("person_id", $barter->person_id)->first();
            if ($mpp) {
              $mpp->quantity_available = $mpp->quantity_available + $bmlip->product_quantity;
              $mpp->save();
            } else {
              $pp = new PersonProduct();
              $pp->geography_id = $BMLIPP->geography_id;
              $pp->geography_type = $BMLIPP->geography_type;
              $pp->dm_id = $BMLIPP->dm_id;
              $pp->person_id = $barter->person_id;
              $pp->product_id = $BMLIPP->product_id;
              $pp->unit_id = $BMLIPP->unit_id;
              $pp->quantity_available = $bmlip->product_quantity;
              $pp->product_lp = $BMLIPP->product_lp;
              $pp->active_on_barterplace = $BMLIPP->active_on_barterplace;
              $pp->save();
            }
            $barter->createBarterLedgerTransactions('Success', $BPledger->id, 'Cr', ($bmlip->product_quantity * $bmlip->product_lp), 'Barter ID: '.$barter->id.' Buy Product '.$bmlip->product->name.' With Quantity '.$bmlip->product_quantity.' From '.$bmlip->barterMatch->person->first_name.' '.$bmlip->barterMatch->person->last_name, $BPledger->balance + ($bmlip->product_quantity * $bmlip->product_lp), $barter->person_id);
          }

          foreach ($barter->barterHaveServices as $bhs) {
            $pr = ($bhs->service_lp * $bhs->no_of_days) * ($dm_margin->dm_margin_percentage / 100);
            if ($dm_margin && $BPledger->balance >= $pr ) {
              
              $barter->createBarterLedgerTransactions('Success', $dmledger->id, 'Cr', $pr, 'DM Margin Barter', $dmledger->balance + $pr, $dm->person_id);
              $dmledgerbal = $dmledger->balance + $pr;
              $dmledger->balance = $dmledgerbal;
              $dmledger->save();

              $BPledger->balance = $BPledger->balance - $pr;
              $BPledger->save();

              sendSMS('Barter ID: ' . $barter->id . ' is complete. Your account is Credited with ' . $pr, $dm->mobile,$smsTempleteId);
              $pr = 0;
            }else{
              return response()->json("Bater Have Person Does Not Have Sufficient Balance To Deduct", 400); 
            }

            $pr = ($bmlip->product_lp * $bmlip->product_quantity) * ($dm_margin->dm_margin_percentage / 100);
            if ($dm_margin && $ledgerbm->balance >= $pr) {
              
              $barter->createBarterLedgerTransactions('Success', $dmledger->id, 'Cr', $pr, 'DM Margin Barter', $dmledger->balance + $pr, $dm->person_id);
              $dmledgerbal = $dmledger->balance + $pr;
              $dmledger->balance = $dmledgerbal;
              $dmledger->save();

              $ledgerbm->balance = $ledgerbm->balance - $pr;
              $ledgerbm->save();

              sendSMS('Barter ID: ' . $barter->id . ' is complete. Your account is Credited with ' . $pr, $dm->mobile,$smsTempleteId);
              $pr = 0;
            }else{
              return response()->json("Bater Match Person Does Not Have Sufficient Balance To Deduct", 400); 
            }

            $barter->createBarterLedgerTransactions('Success', $ledgerbm->id, 'Cr', ($bhs->service_lp * $bhs->no_of_days), 'Barter ID: '.$barter->id.' Buy Service '.$bhs->personService->service->name.' With No Of Days'.$bhs->no_of_days.' From'.$person->first_name.' '.$person->last_name, $ledgerbm->balance + ($bhs->service_lp * $bhs->no_of_days), $bm->person_id);

            $barter->createBarterLedgerTransactions('Success', $BPledger->id, 'Dr', ($bhs->no_of_days * $bhs->service_lp), 'Barter ID: '.$barter->id.' Sell Service '.$bhs->personService->service->name.' With No Of Days '.$bhs->no_of_days.' To '.$bm_person->first_name.' '.$bm_person->last_name, $BPledger->balance - ($bhs->no_of_days * $bhs->service_lp), $barter->person_id);

            $BMLIPP = PersonProduct::where("product_id", $bmlip->product_id)->where("person_id", $bm->person_id)->first();
            $BMLIPP->quantity_available = $BMLIPP->quantity_available - $bmlip->product_quantity;
            $BMLIPP->save();

            $barter->createBarterLedgerTransactions('Success', $ledgerbm->id, 'Dr', ($bmlip->product_lp * $bmlip->product_quantity), 'Barter ID: '.$barter->id.' Sell Product '.$bmlip->product->name.' With Quantity '.$bmlip->product_quantity.' To '.$person->first_name.' '.$person->last_name, $ledgerbm->balance - ($bmlip->product_lp * $bmlip->product_quantity), $bm->person_id);
            

            $mpp = PersonProduct::where("product_id", $bmlip->product_id)->where("person_id", $barter->person_id)->first();
            if ($mpp) {
              $mpp->quantity_available = $mpp->quantity_available + $bmlip->product_quantity;
              $mpp->save();
            } else {
              $pp = new PersonProduct();
              $pp->geography_id = $BMLIPP->geography_id;
              $pp->geography_type = $BMLIPP->geography_type;
              $pp->dm_id = $BMLIPP->dm_id;
              $pp->person_id = $barter->person_id;
              $pp->product_id = $BMLIPP->product_id;
              $pp->unit_id = $BMLIPP->unit_id;
              $pp->quantity_available = $bmlip->product_quantity;
              $pp->product_lp = $BMLIPP->product_lp;
              $pp->active_on_barterplace = $BMLIPP->active_on_barterplace;
              $pp->save();
            }
            $barter->createBarterLedgerTransactions('Success', $BPledger->id, 'Cr', ($bmlip->product_quantity * $bmlip->product_lp), 'Barter ID: '.$barter->id.' Buy Product '.$bmlip->product->name.' With Quantity '.$bmlip->product_quantity.' From '.$bmlip->barterMatch->person->first_name.' '.$bmlip->barterMatch->person->last_name, $BPledger->balance - ($bmlip->product_quantity * $bmlip->product_lp), $barter->person_id);            
          }

          foreach ($barter->barterHaveLp as $bhl) {

            if (($BPledger->balance - $bhl->lp) >= $tempLP) {

              $barter->createBarterLedgerTransactions('Success', $BPledger->id, 'Dr', $bhl->lp, 'Barter ID: '.$barter->id.'Sell LP '.$bhl->lp.' To '.$bmlip->barterMatch->person->first_name.' '.$bmlip->barterMatch->person->last_name, $BPledger->balance - $bhl->lp, $barter->person_id);
             
              $BPledger->balance = $BPledger->balance - $bhl->lp;
              $BPledger->save();

              $barter->createBarterLedgerTransactions('Success', $ledgerbm->id, 'Cr', $bhl->lp, 'Barter ID: '.$barter->id.' Buy LP '.$bhl->lp.' From '.$person->first_name.' '.$person->last_name, $ledgerbm->balance + $bhl->lp, $bm->person_id);

              $ledgerbm->balance = $ledgerbm->balance + $bhl->lp;
              $ledgerbm->save();


              $BMLIPP = PersonProduct::where("product_id", $bmlip->product_id)->where("person_id", $bm->person_id)->first();
              $BMLIPP->quantity_available = $BMLIPP->quantity_available - $bmlip->product_quantity;
              $BMLIPP->save();

              $barter->createBarterLedgerTransactions('Success', $ledgerbm->id, 'Dr', ($bmlip->product_lp * $bmlip->product_quantity), 'Barter ID: '.$barter->id.' Sell Product '.$bmlip->product->name.' With Quantity '.$bmlip->product_quantity.' To '. $person->first_name.' '.$person->last_name, $ledgerbm->balance - ($bmlip->product_lp * $bmlip->product_quantity), $bm->person_id);

              $mpp = PersonProduct::where("product_id", $bmlip->product_id)->where("person_id", $barter->person_id)->first();
              if ($mpp) {
                $mpp->quantity_available = $mpp->quantity_available + $bmlip->product_quantity;
                $mpp->save();
              } else {
                $pp = new PersonProduct();
                $pp->geography_id = $BMLIPP->geography_id;
                $pp->geography_type = $BMLIPP->geography_type;
                $pp->dm_id = $BMLIPP->dm_id;
                $pp->person_id = $barter->person_id;
                $pp->product_id = $BMLIPP->product_id;
                $pp->unit_id = $BMLIPP->unit_id;
                $pp->quantity_available = $bmlip->product_quantity;
                $pp->product_lp = $BMLIPP->product_lp;
                $pp->active_on_barterplace = $BMLIPP->active_on_barterplace;
                $pp->save();
              }
              $barter->createBarterLedgerTransactions('Success', $BPledger->id, 'Cr', ($bmlip->product_quantity * $bmlip->product_lp), 'Barter ID: '.$barter->id.' Buy Product '.$bmlip->product->name.' With Quantity '.$bmlip->product_quantity.'  From '.$bmlip->barterMatch->person->first_name.' '.$bmlip->barterMatch->person->last_name, $BPledger->balance + ($bmlip->product_quantity * $bmlip->product_lp), $barter->person_id);

              if ($dm_margin) {
                $pr = ($bmlip->product_lp * $bmlip->product_quantity) * ($dm_margin->dm_margin_percentage / 100);
                $barter->createBarterLedgerTransactions('Success', $dmledger->id, 'Cr', $pr, 'DM Margin Barter', $dmledger->balance + $pr, $dm->person_id);
                $dmledgerbal = $dmledger->balance + $pr;
                $dmledger->balance = $dmledgerbal;
                $dmledger->save();

                $ledgerbm->balance = $ledgerbm->balance - $pr;
                $ledgerbm->save();

                sendSMS('Barter ID: ' . $barter->id . ' is complete. Your account is Credited with ' . $pr, $dm->mobile,$smsTempleteId);
                $pr = 0;
              }
            } else {
              return response()->json("Barter Person Have Not Enough LP.", 400);
            }
          }
        }
      }
    } else if (count($BMLIS)) {
      $BM = BarterMatch::orWhere([["local_inventory_type", "service"], ["barter_id", $barter->id]])->get();

      foreach ($BM as $bm) {
        $dmledgerbm = Ledger::find($dm->ledger_id);
        $bm_person = Person::find($bm->person_id);
        $ledgerbm = Ledger::find($bm_person->ledger_id);

        $bmlis = $bm->barterMatchLocalInventoryServices;
        if ($bmlis) {
          foreach ($barter->barterHaveProducts as $bhp) {
            $BHPP = PersonProduct::find($bhp->person_product_id);
            $pr = ($bhp->product_lp * $bhp->quantity) * ($dm_margin->dm_margin_percentage / 100);
            if ($dm_margin && $BPledger->balance >= $pr) {
              
              $barter->createBarterLedgerTransactions('Success', $dmledger->id, 'Cr', $pr, 'DM Margin Barter', $dmledger->balance + $pr, $dm->person_id);
              $dmledgerbal = $dmledger->balance + $pr;
              $dmledger->balance = $dmledgerbal;
              $dmledger->save();

              $BPledger->balance = $BPledger->balance - $pr;
              $BPledger->save();

              sendSMS('Barter ID: ' . $barter->id . ' is complete. Your account is Credited with ' . $pr, $dm->mobile,$smsTempleteId);
              $pr = 0;
            }else{
              return response()->json("Bater Have Person Does Not Have Sufficient Balance To Deduct", 400); 
            }
            $pr = ($bmlis->no_of_days * $bmlis->service_lp) * ($dm_margin->dm_margin_percentage / 100);
            if ($dm_margin && $ledgerbm->balance >= $pr) {
              
              $barter->createBarterLedgerTransactions('Success', $dmledger->id, 'Cr', $pr, 'DM Margin Barter', $dmledger->balance + $pr, $dm->person_id);
              $dmledgerbal = $dmledger->balance + $pr;
              $dmledger->balance = $dmledgerbal;
              $dmledger->save();

              $ledgerbm->balance = $ledgerbm->balance - $pr;
              $ledgerbm->save();

              sendSMS('Barter ID: ' . $barter->id . ' is complete. Your account is Credited with ' . $pr, $dm->mobile,$smsTempleteId);
              $pr = 0;
            }else{
              return response()->json("Bater Match Person Does Not Have Sufficient Balance To Deduct", 400); 
            }

            $BHPP->quantity_available = $BHPP->quantity_available - $bhp->quantity;
            $BHPP->save();

            $barter->createBarterLedgerTransactions('Success', $BPledger->id, 'Dr', ($bhp->product_lp * $bhp->quantity), 'Barter ID: '.$barter->id.' Sell Product '.$bhp->personProduct->product->name.' With Quantity '.$bhp->quantity.' To '.$bmlis->barterMatch->person->first_name.' '.$bmlis->barterMatch->person->last_name, $BPledger->balance - ($bhp->product_lp * $bhp->quantity), $barter->person_id);


            $mpp = PersonProduct::where("product_id", $BHPP->product_id)->where("person_id", $bm->person_id)->first();
            if ($mpp) {
              $mpp->quantity_available = $mpp->quantity_available + $bhp->quantity;
              $mpp->save();
            } else {
              $pp = new PersonProduct();
              $pp->geography_id = $BHPP->geography_id;
              $pp->geography_type = $BHPP->geography_type;
              $pp->dm_id = $BHPP->dm_id;
              $pp->person_id = $bm->person_id;
              $pp->product_id = $BHPP->product_id;
              $pp->unit_id = $BHPP->unit_id;
              $pp->quantity_available = $bhp->quantity;
              $pp->product_lp = $BHPP->product_lp;
              $pp->active_on_barterplace = $BHPP->active_on_barterplace;
              $pp->save();
            }
            $barter->createBarterLedgerTransactions('Success', $ledgerbm->id, 'Cr', ($bhp->quantity * $bhp->product_lp), 'Barter ID: '.$barter->id.' Buy Product '.$bhp->personProduct->product->name.' With Quantity: '.$bhp->quantity.' From '.$person->first_name.' '.$person->last_name, $ledgerbm->balance - ($bhp->quantity * $bhp->product_lp), $bm->person_id);
           

            $barter->createBarterLedgerTransactions('Success', $BPledger->id, 'Cr', ($bmlis->no_of_days * $bmlis->service_lp), 'Barter ID: '.$barter->id.' Buy Service '.$bmlis->service->name.' With No. Of Days '.$bmlis->no_of_days.' From '.$bm_person->first_name.' '.$bm_person->last_name, $BPledger->balance + ($bmlis->no_of_days * $bmlis->service_lp), $person->id);

            $barter->createBarterLedgerTransactions('Success', $ledgerbm->id, 'Dr', ($bmlis->no_of_days * $bmlis->service_lp), 'Barter ID: '.$barter->id.' Sell Service '.$bmlis->service->name.' With No. Of Days '.$bmlis->no_of_days.' To '.$person->first_name.' '.$person->last_name, $ledgerbm->balance - ($bmlis->no_of_days * $bmlis->service_lp), $bm->person_id);
          }
          foreach ($barter->barterHaveServices as $bhs) {
            $pr = ($bhs->service_lp * $bhs->no_of_days) * ($dm_margin->dm_margin_percentage / 100);
            if ($dm_margin && $BPledger->balance >= $pr) {
              
              $barter->createBarterLedgerTransactions('Success', $dmledger->id, 'Cr', $pr, 'DM Margin Barter', $dmledger->balance + $pr, $dm->person_id);
              $dmledgerbal = $dmledger->balance + $pr;
              $dmledger->balance = $dmledgerbal;
              $dmledger->save();

              $BPledger->balance = $BPledger->balance - $pr;
              $BPledger->save();

              sendSMS('Barter ID: ' . $barter->id . ' is complete. Your account is Credited with ' . $pr, $dm->mobile,$smsTempleteId);
              $pr = 0;
            }else{
              return response()->json("Bater Have Person Does Not Have Sufficient Balance To Deduct", 400); 
            }

            $pr = ($bmlis->no_of_days * $bmlis->service_lp) * ($dm_margin->dm_margin_percentage / 100);
            if ($dm_margin && $ledgerbm->balance >= $pr) {
              
              $barter->createBarterLedgerTransactions('Success', $dmledger->id, 'Cr', $pr, 'DM Margin Barter', $dmledger->balance + $pr, $dm->person_id);
              $dmledgerbal = $dmledger->balance + $pr;
              $dmledger->balance = $dmledgerbal;
              $dmledger->save();

              $ledgerbm->balance = $ledgerbm->balance - $pr;
              $ledgerbm->save();

              sendSMS('Barter ID: ' . $barter->id . ' is complete. Your account is Credited with ' . $pr, $dm->mobile,$smsTempleteId);
              $pr = 0;
            }else{
              return response()->json("Bater Match Person Does Not Have Sufficient Balance To Deduct", 400); 
            }
            $barter->createBarterLedgerTransactions('Success', $ledgerbm->id, 'Cr', ($bhs->service_lp * $bhs->no_of_days), 'Barter ID: '.$barter->id.' Buy Service '.$bhs->personService->service->name.' With No Of Days '.$bhs->no_of_days.' From '.$person->first_name.' '.$person->last_name, $ledgerbm->balance + ($bhs->service_lp * $bhs->no_of_days), $bm->person_id);

            $barter->createBarterLedgerTransactions('Success', $BPledger->id, 'Dr', ($bhs->no_of_days * $bhs->service_lp), 'Barter ID: '.$barter->id.' Sell Service '.$bhs->personService->service->name .' With No Of Days '.$bhs->no_of_days.' To '.$bm_person->first_name.' '.$bm_person->last_name, $BPledger->balance - ($bhs->no_of_days * $bhs->service_lp), $barter->person_id);

            $barter->createBarterLedgerTransactions('Success', $ledgerbm->id, 'Dr', ($bmlis->no_of_days * $bmlis->service_lp), 'Barter ID: '.$barter->id.' Sell Service '.$bmlis->service->name.' With No Of Days'.$bmlis->no_of_days.' To '. $person->first_name.' '.$person->last_name, $ledgerbm->balance - ($bmlis->no_of_days * $bmlis->service_lp), $bm->person_id);

            $barter->createBarterLedgerTransactions('Success', $BPledger->id, 'Cr', ($bmlis->no_of_days * $bmlis->service_lp), 'Barter ID: '.$barter->id.' Buy Service '.$bmlis->service->name.' With No Of Days'.$bmlis->no_of_days.' From '.$bmlis->barterMatch->person->first_name.' '.$bmlis->barterMatch->person->last_name, $BPledger->balance + ($bmlis->no_of_days * $bmlis->service_lp), $barter->person_id);
          }
          foreach ($barter->barterHaveLp as $bhl) {

            if (($BPledger->balance - $bhl->lp) >= $tempLP) {

              $barter->createBarterLedgerTransactions('Success', $ledgerbm->id, 'Cr', $bhl->lp, 'Barter ID: '.$barter->id.' Buy LP '.$bhl->lp.' From '.$person->first_name.' '.$person->last_name, $ledgerbm->balance + $bhl->lp, $bm->person_id);
              $ledgerbm->balance = $ledgerbm->balance + $bhl->lp;
              $ledgerbm->save();

              $barter->createBarterLedgerTransactions('Success', $BPledger->id, 'Dr', $bhl->lp, 'Barter ID: '.$barter->id.' Sell LP '. $bhl->lp . ' To'.$bmlis->barterMatch->person->first_name.' '.$bmlis->barterMatch->person->last_name, $BPledger->balance - $bhl->lp, $barter->person_id);
              $BPledger->balance = $BPledger->balance - $bhl->lp;
              $BPledger->save();

              $barter->createBarterLedgerTransactions('Success', $BPledger->id, 'Cr', ($bmlis->no_of_days * $bmlis->service_lp), 'Barter ID: '.$barter->id.' Buy Service '.$bmlis->service->name.' With No Of Days'.$bmlis->no_of_days.' From '.$bmlis->barterMatch->person->first_name.' '.$bmlis->barterMatch->person->last_name, $ledgerbm->balance + ($bmlis->no_of_days * $bmlis->service_lp), $barter->person_id);
              

              $barter->createBarterLedgerTransactions('Success', $ledgerbm->id, 'Dr', ($bmlis->no_of_days * $bmlis->service_lp), 'Barter ID: '.$barter->id.' Sell Service '.$bmlis->service->name.' With No Of Days '.$bmlis->no_of_days.' To '.$person->first_name.' '.$person->last_name, $BPledger->balance - ($bmlis->no_of_days * $bmlis->service_lp), $bm->person_id);

              if ($dm_margin) {
                $pr = ($bmlis->no_of_days * $bmlis->service_lp) * ($dm_margin->dm_margin_percentage / 100);
                $barter->createBarterLedgerTransactions('Success', $dmledger->id, 'Cr', $pr, 'DM Margin Barter', $dmledger->balance + $pr, $dm->person_id);
                $dmledgerbal = $dmledger->balance + $pr;
                $dmledger->balance = $dmledgerbal;
                $dmledger->save();

                $ledgerbm->balance = $ledgerbm->balance - $pr;
                $ledgerbm->save();

                sendSMS('Barter ID: ' . $barter->id . ' is complete. Your account is Credited with ' . $pr, $dm->mobile,$smsTempleteId);
                $pr = 0;
              }
            } else {
              return response()->json("Barter Person Have Not Enough LP.", 400);
            }
          }
        }
      }
    } else if (count($BMLIL)) {
      // Log::info("BMLIL " . $barter->id);
      $BM = BarterMatch::orWhere([["local_inventory_type", "lp"], ["barter_id", $barter->id]])->get();

      foreach ($BM as $bm) {
        $dmledgerbm = Ledger::find($dm->ledger_id);
        $bm_person = Person::find($bm->person_id);
        $ledgerbm = Ledger::find($bm_person->ledger_id);

        $bmlil = $bm->barterMatchLocalInventoryLps;
        if ($bmlil) {
          foreach ($barter->barterHaveProducts as $bhp) {

            if (($ledgerbm->balance - $bmlil->lp) >= $tempLP) {

              $BHPP = PersonProduct::find($bhp->person_product_id);
              $BHPP->quantity_available = $BHPP->quantity_available - $bhp->quantity;
              $BHPP->save();

              $barter->createBarterLedgerTransactions('Success', $BPledger->id, 'Dr', ($bhp->product_lp * $bhp->quantity), 'Barter ID: '.$barter->id.' Sell Product '.$bhp->personProduct->product->name.' With Quantity '.$bhp->quantity.' To '.$bmlil->barterMatch->person->first_name.' '.$bmlil->barterMatch->person->last_name, $BPledger->balance - ($bhp->product_lp * $bhp->quantity), $barter->person_id);

              if ($dm_margin) {
                $pr = ($bhp->product_lp * $bhp->quantity) * ($dm_margin->dm_margin_percentage / 100);
                $barter->createBarterLedgerTransactions('Success', $dmledger->id, 'Cr', $pr, 'DM Margin Barter', $dmledger->balance + $pr, $dm->person_id);
                $dmledgerbal = $dmledger->balance + $pr;
                $dmledger->balance = $dmledgerbal;
                $dmledger->save();

                $BPledger->balance = $BPledger->balance - $pr;
                $BPledger->save();

                sendSMS('Barter ID: ' . $barter->id . ' is complete. Your account is Credited with ' . $pr, $dm->mobile,$smsTempleteId);
                $pr = 0;
              }

              $mpp = PersonProduct::where("product_id", $BHPP->product_id)->where("person_id", $bm->person_id)->first();
              if ($mpp) {
                $mpp->quantity_available = $mpp->quantity_available + $bhp->quantity;
                $mpp->save();
              } else {
                $pp = new PersonProduct();
                $pp->geography_id = $BHPP->geography_id;
                $pp->geography_type = $BHPP->geography_type;
                $pp->dm_id = $BHPP->dm_id;
                $pp->person_id = $bm->person_id;
                $pp->product_id = $BHPP->product_id;
                $pp->unit_id = $BHPP->unit_id;
                $pp->quantity_available = $bhp->quantity;
                $pp->product_lp = $BHPP->product_lp;
                $pp->active_on_barterplace = $BHPP->active_on_barterplace;
                $pp->save();
              }
              $barter->createBarterLedgerTransactions('Success', $ledgerbm->id, 'Cr', ($bhp->quantity * $bhp->product_lp), 'Barter ID: '.$barter->id.' Buy Product '.$bhp->personProduct->product->name.' With Quantity '.$bhp->quantity.' From '.$person->first_name.' '.$person->last_name, $ledgerbm->balance + ($bhp->quantity * $bhp->product_lp), $bm->person_id);
              

              $barter->createBarterLedgerTransactions('Success', $ledgerbm->id, 'Dr', $bmlil->lp, 'Barter ID: '.$barter->id.' Sell LP '.$bmlil->lp.' To '.$person->first_name.' '.$person->last_name, $ledgerbm->balance - $bmlil->lp, $bm->person_id);
              $ledgerbm->balance = $ledgerbm->balance - $bmlil->lp;
              $ledgerbm->save();

              $barter->createBarterLedgerTransactions('Success', $BPledger->id, 'Cr', $bmlil->lp, 'Barter ID: '.$barter->id.' Buy LP '.$bmlil->lp. ' From ' . $bm_person->first_name.' '.$bm_person->last_name, $BPledger->balance + $bmlil->lp, $barter->person_id);
              $BPledger->balance = $BPledger->balance + $bmlil->lp;
              $BPledger->save();

            } else {
              return response()->json("Match Person Have Not Enough LP.", 400);
            }
          }

          foreach ($barter->barterHaveServices as $bhs) {

            if (($ledgerbm->balance - $bmlil->lp) >= $tempLP) {

              $barter->createBarterLedgerTransactions('Success', $BPledger->id, 'Dr', ($bhs->service_lp * $bhs->no_of_days), 'Barter ID: '.$barter->id.' Sell Service '.$bhs->personService->service->name.' With No Of Days '.$bhs->no_of_days.' To '.$bm_person->first_name.' '.$bm_person->last_name, $BPledger->balance - ($bhs->service_lp * $bhs->no_of_days), $barter->person_id);

              $barter->createBarterLedgerTransactions('Success', $ledgerbm->id, 'Cr', ($bhs->no_of_days * $bhs->service_lp), 'Barter ID: '.$barter->id.' Buy Service '.$bhs->personService->service->name.' With No Of Days '.$bhs->no_of_days.' From'.$person->first_name.' '.$person->last_name, $ledgerbm->balance + ($bhs->no_of_days * $bhs->service_lp), $bm->person_id);

              if ($dm_margin) {
                $pr = ($bhs->service_lp * $bhs->no_of_days) * ($dm_margin->dm_margin_percentage / 100);
                $barter->createBarterLedgerTransactions('Success', $dmledger->id, 'Cr', $pr, 'DM Margin Barter', $dmledger->balance + $pr, $dm->person_id);
                $dmledgerbal = $dmledger->balance + $pr;
                $dmledger->balance = $dmledgerbal;
                $dmledger->save();

                $BPledger->balance = $BPledger->balance - $pr;
                $BPledger->save();

                sendSMS('Barter ID: ' . $barter->id . ' is complete. Your account is Credited with ' . $pr, $dm->mobile,$smsTempleteId);
                $pr = 0;
              }

              $barter->createBarterLedgerTransactions('Success', $ledgerbm->id, 'Dr', $bmlil->lp, 'Barter ID: '.$barter->id.' Sell LP '.$bmlil->lp.' To'.$person->first_name.' '.$person->last_name, $ledgerbm->balance - $bmlil->lp, $bm->person_id);
              $ledgerbm->balance = $ledgerbm->balance - $bmlil->lp;
              $ledgerbm->save();

              $barter->createBarterLedgerTransactions('Success', $BPledger->id, 'Cr', $bmlil->lp, 'Barter ID: '.$barter->id.' Buy LP'.$bmlil->lp.' From '.$bm_person->first_name.' '.$bm_person->last_name, $BPledger->balance + $bmlil->lp, $barter->person_id);
              $BPledger->balance = $BPledger->balance + $bmlil->lp;
              $BPledger->save();

            } else {
              return response()->json("Match Person Have Not Enough LP.", 400);
            }
          }

          foreach ($barter->barterHaveLp as $bhl) {

            if ((($ledgerbm->balance - $bmlil->lp) >= $tempLP) && (($BPledger->balance - $bhl->lp) >= $tempLP)) {

              $barter->createBarterLedgerTransactions('Success', $BPledger->id, 'Dr', $bhl->lp, 'Barter ID: '.$barter->id.' Sell LP '.$bhl->lp.' To '.$bmlil->barterMatch->person->first_name.' '.$bmlil->barterMatch->person->last_name, $BPledger->balance - $bhl->lp, $barter->person_id);

              $BPledger->balance = $BPledger->balance - $bhl->lp;
              $BPledger->save();

              $barter->createBarterLedgerTransactions('Success', $ledgerbm->id, 'Cr', $bhl->lp, 'Barter ID: '.$barter->id.' Buy LP '.$bhl->lp.' From '.$person->first_name.' '.$person->last_name, $ledgerbm->balance + $bhl->lp, $bm->person_id);

              $ledgerbm->balance = $ledgerbm->balance + $bhl->lp;
              $ledgerbm->save();


              $barter->createBarterLedgerTransactions('Success', $ledgerbm->id, 'Dr', $bmlil->lp, 'Barter ID: '.$barter->id.' Sell LP '.$bmlil->lp.' To '.$person->first_name.' '.$person->last_name, $ledgerbm->balance - $bmlil->lp, $bm->person_id);

              $ledgerbm->balance = $ledgerbm->balance - $bmlil->lp;
              $ledgerbm->save();

              $barter->createBarterLedgerTransactions('Success', $BPledger->id, 'Cr', $bmlil->lp, 'Barter ID: '.$barter->id.' Buy LP '.$bmlil->lp.' From '.$bmlil->barterMatch->person->first_name.' '.$bmlil->barterMatch->person->last_name, $BPledger->balance + $bmlil->lp, $barter->person_id);

              $BPledger->balance = $BPledger->balance + $bmlil->lp;
              $BPledger->save();
            } else {
              return response()->json("Barter Person Or Match Person Have Not Enough LP.", 400);
            }
          }
        }
      }
    }
    $barter->save();
    return response()->json($barter, 200);
  }
  public function barterUnlock($barter_id)
  {
    $barter = Barter::find($barter_id);
    $barter->status = 'Open';
    $barter->save();
    return response()->json($barter, 200);
  }
  public function confirmPersonStatus($id)
  {
    $bc = BarterConfirmation::find($id);
    $bc->status = 'Confirmed';
    $bc->confirmation_type = 'Manual';
    $bc->confirmation_time =  Carbon::now();
    $bc->save();
    $barter = $bc->barter;
    if ($barter) {
      $bcs = BarterConfirmation::where('barter_id', $barter->id)->get();
      $flag = false;
      foreach ($bcs as $bct) {
        if ($bct->status != 'Confirmed') {
          $flag = true;
        }
      }
      if (!$flag) {
        $barter->status = 'Confirmed';
        $barter->save();
      }
    }
    return response()->json($bc, 200);
  }

  public function barterMatchConfirmByPerson(Request $request, $id)
  {
    $bc = BarterConfirmation::find($id);
    $bc->status = $request['status'];
    $bc->confirmation_time = Carbon::now();
    $bc->confirmation_type = 'SMS Link';
    $bc->save();
    return response()->json(true, 200);
  }
}
