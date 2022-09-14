<?php

namespace App\Http\Controllers;

use App\Barter;
use Illuminate\Http\Request;
use App\Repositories\Repository\BarterRepository;
use Carbon\Carbon;
use App\BarterHaveProduct;
use App\BarterHaveService;
use App\BarterHaveLp;
use App\BarterNeedProduct;
use App\BarterNeedService;
use App\BarterNeedLp;
use App\Person;
use App\BarterMatch;
use App\PersonProduct;
use App\BarterConfirmation;
use App\DMGeography;
use App\PersonService;
use Log;

class BarterController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  private $repository;

  public function __construct(BarterRepository $repository)
  {
    $this->repository = $repository;
  }
  public function filterBarter(Request $request)
  {
    $barters = Barter::with('drisheeMitras', 'geography', 'person')->orderBy('created_at', 'DESC');
    $geography_ids = $request['geography_ids'];
    // $barters->whereIn('geography_id', $geography_ids);
    // $dms->whereHas('dmGeography',function($query) use($geography_ids) {
    //         $query->whereIn('geography_id', $geography_ids);
    //     });

    if (isset($request['filters']['barter_date_time_added']) && !empty($request['filters']['barter_date_time_added'])) {
      $barters->whereDate('barter_date_time_added', $request['filters']['barter_date_time_added']);
      // ->orWhere('last_name','like','%'.$request['filters']['name'].'%');
    }
    if (isset($request['filters']['barter_expire_date']) && !empty($request['filters']['barter_expire_date'])){
      $barters->where('barter_expire_date', $request['filters']['barter_expire_date']);
    }
    if (isset($request['filters']['status']) && !empty($request['filters']['status'])) {
      $barters->where('status', $request['filters']['status']);
    }
    if(isset($request['filters']['geography_id']) && !empty($request['filters']['geography_id'])) {
      $barters->where('geography_id',$request['filters']['geography_id']);
    }
    if(isset($request['filters']['added_by_dm_id'])  && !empty($request['filters']['added_by_dm_id'])) {
      
      $barters->where('added_by_dm_id',$request['filters']['added_by_dm_id']);
    }
    if(isset($request['count']) && $request['count']) {
      $barters = $barters->get();
      return response()->json(count($barters), 200);
    }
    $offset = isset($request['skip']) ? $request['skip'] : 0;
    $chunk = isset($request['skip']) ? $request['limit'] : 999999;
    $barters = $barters->skip($offset)->limit($chunk)->get();
    return response()->json($barters, 200);
  }
  public function index(Request $request)
  {
    // $barters = Barter::with('drisheeMitras','geography','person');
    //   $geography_ids = $request['geography_ids'];
    //   $barters->whereIn('geography_id',$geography_ids);
    //   // $dms->whereHas('dmGeography',function($query) use($geography_ids) {
    //   //         $query->whereIn('geography_id', $geography_ids);
    //   //     });

    //   if(isset($request['filters']['barter_date_time_added']) && !empty($request['filters']['barter_date_time_added'])) 
    //   {
    //       $barters->whereDate('barter_date_time_added',$request['filters']['barter_date_time_added']);
    //            // ->orWhere('last_name','like','%'.$request['filters']['name'].'%');
    //   }
    //   if(isset($request['filters']['barter_expire_date']) && !empty($request['filters']['barter_expire_date'])) {
    //       $barters->where('barter_expire_date',$request['filters']['barter_expire_date']);

    //   }
    //   if(isset($request['filters']['status']) && !empty($request['filters']['status'])) {
    //       $barters->where('status',$request['filters']['status']);

    //   }
    //   return response()->json($barters->get(),200);
    return response()->json($this->repository->all(), 200);
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

    // dd($request->all());

    Log::info("Barter befor".$request);

    $barter = $request['barter'];
    $barter['barter_date_time_added'] = Carbon::now();
    $bc = Barter::create($barter);
    if ($bc) {
      if ($request['barter_have_products']) {
        foreach ($request['barter_have_products'] as $bhp) {
          $bhp['barter_id'] = $bc->id;
          BarterHaveProduct::create($bhp);
        }
      }
      if ($request['barter_have_services']) {
        foreach ($request['barter_have_services'] as $bhs) {
          $bhs['barter_id'] = $bc->id;

          BarterHaveService::create($bhs);
        }
      }
      if ($request['barter_have_lps']) {
        foreach ($request['barter_have_lps'] as $bhl) {
          $bhl['barter_id'] = $bc->id;
          BarterHaveLp::create($bhl);
        }
      }
      if ($request['barter_need_products']) {
        foreach ($request['barter_need_products'] as $bnp) {
          $bnp['barter_id'] = $bc->id;
          BarterNeedProduct::create($bnp);
        }
      }
      if ($request['barter_need_services']) {
        foreach ($request['barter_need_services'] as $bns) {
          $bns['barter_id'] = $bc->id;
          BarterNeedService::create($bns);
        }
      }
      if ($request['barter_need_lps']) {
        foreach ($request['barter_need_lps'] as $bnl) {
          $bnl['barter_id'] = $bc->id;
          BarterNeedLp::create($bnl);
        }
      }
    }
    Log::info("Barter after".$request);
    // $request['barter_date_time_added'] = Carbon::now();
    // dd($request->all());
    return response()->json($bc->load('barterHaveServices', 'barterHaveProducts', 'barterHaveLp', 'person'), 201);
  }

  /**
   * Display the specified resource.
   *
   * @param  \App\Barter  $barter
   * @return \Illuminate\Http\Response
   */
  public function show(Barter $barter)
  {
    // dd($barter);
    $barter = Barter::find($barter->id)->load(
      'barterHaveServices.personService.service',
      'barterHaveProducts.personProduct.product.units',
      'barterHaveLp',
      'barterNeedProducts.product.units',
      'barterNeedServices.service',
      'barterNeedLp',
      'person.personPersonalDetails',
      'barterMatchLocalInventoryLps',
      'barterMatchLocalInventoryServices.service',
      'barterMatchLocalInventoryProducts.product'
    );
    if ($barter->barter_expire_date) {
      $exp_date = $barter->barter_expire_date;
      $barter->barter_expire_date = Carbon::parse($exp_date)->format('Y-m-d');
      //$exp_date = $barter->barter_expire_date;
    }
    // $barter->expiry_date = 
    // return response()->json($this->repository->findById($barter->id)->load('barterHaveServices.personService.service','barterHaveProducts.personProduct.product.units','barterHaveLp','barterNeedProducts.product.units','barterNeedServices.service','barterNeedLp','person.personPersonalDetails'),201);
    return response()->json($barter, 200);
  }

  public function getBarter($id)
  {
    $barter = Barter::find($id)->load(
      'barterMatchLocalInventoryLps',
      'barterMatchLocalInventoryServices.barter.barterMatches.person',
      'barterMatchLocalInventoryServices.service',
      // 'barterMatchLocalInventoryServices.service',
      'barterMatchLocalInventoryProducts.barter.barterMatches.person',
      'barterMatchLocalInventoryProducts.product'
    );
    // $d = $d->barterMatchLocalInventoryServices"=>$barter->barterMatchLocalInventoryServices);
    // array_push($barter['barter_match_local_inventory_services'],{"person":$barter->barterMatches->person});
    // $d = 
    return response()->json($barter, 200);
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  \App\Barter  $barter
   * @return \Illuminate\Http\Response
   */
  public function edit(Barter $barter)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \App\Barter  $barter
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, $id)
  {
    $barter = $request['barter'];
    // $barter['barter_date_time_added'] = Carbon::now();
    $bc = Barter::find($id);
    if ($bc) {
      $bc->update($barter);
    }
    // $bc = Barter::create($barter);
    if ($bc->status == 'Open') {
      if ($request['barter_have_products']) {
        foreach ($request['barter_have_products'] as $bhp) {
          $bhp['barter_id'] = $bc->id;
          // dd($bhp->id);
          if (isset($bhp['id'])) {
            $bhpu = BarterHaveProduct::find($bhp['id']);
            $bhpu->update($bhp);
          } else {
            BarterHaveProduct::create($bhp);
          }
        }
      }
      if ($request['barter_have_services']) {
        foreach ($request['barter_have_services'] as $bhs) {
          $bhs['barter_id'] = $bc->id;

          if (isset($bhs['id'])) {
            $bhsu = BarterHaveService::find($bhs['id']);
            $bhsu->update($bhs);
          } else {
            BarterHaveService::create($bhs);
          }
        }
      }
      if ($request['barter_have_lps']) {
        foreach ($request['barter_have_lps'] as $bhl) {
          $bhl['barter_id'] = $bc->id;
          if (isset($bhl['id'])) {
            $bhlu = BarterHaveLp::find($bhl['id']);

            $bhlu->update($bhl);
          } else {
            BarterHaveLp::create($bhl);
          }
        }
      }
      if ($request['barter_need_products']) {
        foreach ($request['barter_need_products'] as $bnp) {
          $bnp['barter_id'] = $bc->id;
          if (isset($bnp['id'])) {
            $bnpu = BarterNeedProduct::find($bnp['id']);

            $bnpu->update($bnp);
          } else {
            BarterNeedProduct::create($bnp);
          }
        }
      }
      if ($request['barter_need_services']) {
        foreach ($request['barter_need_services'] as $bns) {

          $bns['barter_id'] = $bc->id;
          if (isset($bns['id'])) {
            $bnsu = BarterNeedService::find($bns['id']);

            $bnsu->update($bns);
          } else {
            BarterNeedService::create($bns);
          }
        }
      }
      if ($request['barter_need_lps']) {
        foreach ($request['barter_need_lps'] as $bnl) {
          $bnl['barter_id'] = $bc->id;

          if (isset($bnl['id'])) {
            $bnlu = BarterNeedLp::find($bnl['id']);

            $bnlu->update($bnl);
          } else {

            BarterNeedLp::create($bnl);
          }
        }
      }
    }
    // $request['barter_date_time_added'] = Carbon::now();
    // dd($request->all());
    return response()->json($bc->load('barterHaveServices', 'barterHaveProducts.personProduct.product', 'barterHaveLp', 'barterNeedProducts', 'barterNeedServices', 'barterNeedLp', 'person'), 201);
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  \App\Barter  $barter
   * @return \Illuminate\Http\Response
   */
  public function destroy(Barter $barter)
  {
    $barter = Barter::find($barter->id);
    if ($barter->status == 'Open') {
      $barter->destroy($barter->id);
      return response()->json(true, 201);
    } else {
      return response()->json(['error' => 'Can not delete this barter'], 405);
    }
    return response()->json(['error' => 'Barter_Not_Found'], 404);
  }
  public function barterHaveProductDelete($id)
  {
    $ba = BarterHaveProduct::find($id);
    if ($ba) {
      $ba->destroy($id);
      return response()->json($ba, 200);
    }
    return response()->json(['error' => 'Not Found'], 404);
  }
  public function barterHaveServiceDelete($id)
  {
    $bs = BarterHaveService::find($id);
    if ($bs) {
      $bs->destroy($id);
      return response()->json($bs, 201);
    }
    return response()->json(['error' => 'Not Found'], 404);
  }
  public function barterHaveLpDelete($id)
  {
    $bhlp = BarterHaveLp::find($id);
    if ($bhlp) {
      $bhlp->destroy($id);
      return response()->json($bhlp, 201);
    }
    return response()->json(['error' => 'Not Found'], 404);
  }
  public function barterNeedProductDelete($id)
  {
    $ba = BarterNeedProduct::find($id);
    if ($ba) {
      $ba->destroy($id);
      return response()->json($ba, 200);
    }
    return response()->json(['error' => 'Not Found'], 404);
  }
  public function barterNeedServiceDelete($id)
  {
    $bs = BarterNeedService::find($id);
    if ($bs) {
      $bs->destroy($id);
      return response()->json($bs, 201);
    }
    return response()->json(['error' => 'Not Found'], 404);
  }
  public function barterNeedLpDelete($id)
  {
    $bhlp = BarterNeedLp::find($id);
    if ($bhlp) {
      $bhlp->destroy($id);
      return response()->json($bhlp, 201);
    }
    return response()->json(['error' => 'Not Found'], 404);
  }
  // public function barterNeedProductSave()
  // {
  //     $bnp = BarterNeedProduct::create()
  // }
  public function getAllBarterOfDm($dm_id)
  {
    $barters = Barter::where('added_by_dm_id', $dm_id)->get();
    return response()->json($barters->load('barterHaveServices.personService.service', 'barterHaveProducts.personProduct.product.units', 'barterHaveLp', 'barterNeedProducts.product.units', 'barterNeedServices.service', 'barterNeedLp', 'person.personPersonalDetails'), 200);
  }
  public function getAllActiveBarter($dm_id)
  {
    $barters = Barter::where('added_by_dm_id', $dm_id)->where('status', '!=', 'Completed')->get();
    return response()->json($barters->load('barterHaveServices.personService.service', 'barterHaveProducts.personProduct.product.units', 'barterHaveLp', 'barterNeedProducts.product.units', 'barterNeedServices.service', 'barterNeedLp', 'person.personPersonalDetails'), 200);
  }
  public function getAllProductServiceForBarter($barter_id)
  {
    $barter = Barter::where('id', $barter_id)->where('status', '!=', 'Completed')->first();
    // dd($barter->geography_id);
    if ($barter) {
      // dd($barter->geography_id);
      $person = Person::where('geography_id', $barter->geography_id);
      if (empty($barter->barterNeedServices)) {
        // dd();
        $service_lp = $barter->barterNeedServices[0]->service_lp;
        $person->whereHas('personServices', function ($query) use ($service_lp) {
          $query->where('service_lp', '=', $service_lp);
        });
      }
      // dd($person->toSql());
      if (empty($barter->barterNeedProducts)) {
        $product_lp = $barter->barterNeedProducts[0]->product_lp;

        $person->whereHas('personProducts', function ($query) use ($product_lp) {
          $query->where('product_lp', '=', $product_lp);
        });
      }

      // $person = $person->personPersonalDetails();
      return response()->json($person->get()->load('personPersonalDetails', 'personProducts.product.units', 'personServices.service'), 200);
    }
  }
  public function getAllProductServiceForBarterTest($barter_id)
  {
    $barter = Barter::where('id', $barter_id)->where('status', '!=', 'Completed')->first();
    $res = collect();
    $dm_geo = DMGeography::where('dm_id', $barter->added_by_dm_id)->first();
    $geography_id = $dm_geo->geography_id;
    if ($barter) {

      if(count($barter->barterNeedProducts) && count($barter->barterNeedLp)){
        $barter_matches = BarterMatch::where('barter_id', $barter_id)->get()->load('person.personPersonalDetails', 'barterMatchLocalInventoryLps', 'barterMatchLocalInventoryProducts.product.units', 'barterMatchLocalInventoryServices.service');
        $needLp = $barter->barterNeedLp;
        $lp = $needLp[0]->lp;
        foreach ($barter->barterNeedProducts as $bnp) {
          
          if(count($barter_matches)){
            $person = $barter_matches[0]->person_id;
            $product_id = $bnp['product_id'];
            $person = Person::find($barter_matches[0]->person_id);
            $person->person_products = PersonProduct::where('product_id', $product_id)->where('person_id', $barter_matches[0]->person_id)->get()->load('product.units');
            $res->push($person->load('personPersonalDetails','ledger'));
              
          }else{
            $product_lp = $bnp['product_lp'];
            $product_id = $bnp['product_id'];
            $persons = Person::whereHas('personProducts', function ($query) use ($product_lp, $product_id, $geography_id) {
              $query->where('product_lp', '>=', $product_lp);
              $query->where('product_id', '=', $product_id);
              $query->where('geography_id', '=', $geography_id);
            })
            ->whereHas('ledger', function($query) use ($lp){
              // $query->where('balance', '>=', $lp);
            })
            ->where('id', '!=', $barter->person_id)->where('geography_id', '=', $geography_id)->get();
            foreach ($persons as $pp) {
              $pp->person_products = PersonProduct::where('product_id', $product_id)->where('person_id', $pp->id)->get()->load('product.units');
              $res->push($pp->load('personPersonalDetails','ledger'));
            }
          }
        }
        return response()->json($res, 200);
      }
      foreach ($barter->barterNeedProducts as $bnp) {
        $product_lp = $bnp['product_lp'];
        $product_id = $bnp['product_id'];
        $persons =  Person::whereHas('personProducts', function ($query) use ($product_lp, $product_id, $geography_id) {
          $query->where('product_lp', '>=', $product_lp);
          $query->where('product_id', '=', $product_id);
          $query->where('geography_id', '=', $geography_id);
        })->where('id', '!=', $barter->person_id)->get();

        foreach ($persons as $pp) {
          $pp->person_products = PersonProduct::where('product_id', $product_id)->where('person_id', $pp->id)->get()->load('product.units');
          $res->push($pp->load('personPersonalDetails'));
        }
      }
      foreach ($barter->barterNeedServices as $bns) {
        $service_lp = $bns['service_lp'];
        $service_id = $bns['service_id'];
        $persons =  Person::whereHas('personServices', function ($query) use ($service_lp, $geography_id, $service_id) {
          $query->where('service_lp', '=', $service_lp);
          $query->where('service_id', '=', $service_id);
          $query->where('geography_id', '=', $geography_id);
        })->where('id', '!=', $barter->person_id)->get();
        foreach ($persons as $ps) {
          $ps->person_services  = PersonService::where("service_id",$service_id)->where("person_id",$ps->id)->get()->load('service');
          $res->push($ps->load('personPersonalDetails'));
        }
      }
      foreach ($barter->barterNeedLp as $bnl) {
        $lp = $bnl['lp'];
        $persons = Person::whereHas('ledger', function ($query) use ($lp) {
          // $query->where('balance', '>=', $lp);
        })->where('id', '!=', $barter->person_id)->where('geography_id', '=', $geography_id)->get();
        foreach ($persons as $p) {
          $res->push($p->load('personPersonalDetails', 'ledger'));
        }
      }
      return response()->json($res, 200);
    }
  }
  public function getPersonBarter($person_id)
  {
    $barter = Barter::where('person_id', $person_id)->get()->load('barterHaveServices.personService.service', 'barterHaveProducts.personProduct.product.units', 'barterHaveLp', 'barterNeedProducts.product.units', 'barterNeedServices.service', 'barterNeedLp', 'person.personPersonalDetails');
    return response()->json($barter, 200);
  }
  public function bartetMatchGetSingle($id)
  {
    // $barter = Barter::find($id)->load('barterMatches.barterMatchLocalInventoryLps','barterMatches.barterMatchLocalInventoryProducts.product.units','barterMatches.barterMatchLocalInventoryServices.service','barterMatches.person');
    // // dd($barter);
    // $barter_match['barter_id'] = $barter->id;
    // dd($barter->barterMatches);
    // $res = 
    // foreach ($barter->barterMatches as $bm) {

    // }
    // $barter_match['match_type'] = $barter->barterMatches && $barter->barterMatches->match_type
    // ? $barter->barterMatches->match_type:null;
    // $barter_match['barter_2_id'] = $barter->barterMatches && $barter->barterMatches->barter_2_id ? $barter->barterMatches->barter_2_id:null;
    // $barter_match['person_id'] = $barter->barterMatches && $barter->barterMatches->person_id ? $barter->barterMatches->person_id:null;
    // $barter_match['total_lp_offered'] = $barter->barterMatches && $barter->barterMatches->total_lp_offered ? $barter->barterMatches->total_lp_offered:null;
    // $barter_match['local_inventory_type'] = $barter->barterMatches && $barter->barterMatches->local_inventory_type ? $barter->barterMatches->local_inventory_type:null;
    // $barter_match['person'] = $barter->barterMatches && $barter->barterMatches->person ? $barter->barterMatches->person->first_name ." ". ($barter->barterMatches->person && $barter->barterMatches->person->middle_name ? $barter->barterMatches->person->middle_name : null) ."".($barter->barterMatches->person && $barter->barterMatches->person->last_name ? $barter->barterMatches->person->last_name : null) ; 
    $barter_matches = BarterMatch::where('barter_id', $id)->get()->load('person.personPersonalDetails', 'barterMatchLocalInventoryLps', 'barterMatchLocalInventoryProducts.product.units', 'barterMatchLocalInventoryServices.service');
    return response()->json($barter_matches, 200);
  }
  public function getBarterConfirmation($barter_id)
  {
    $bc = BarterConfirmation::where('barter_id', $barter_id)->get()->load('person');
    return response()->json($bc, 200);
  }
  public function getBarterTransactions($id)
  {
    $barter = Barter::find($id);
    // $person = Person::find($barter->person_id);
    // dd($person->ledgerTransactions); 
    $res  = $barter->ledgerTransactions->groupBy('person_id');
    $rescollect = collect();
    $temp = array();
    foreach ($res as $key => $value) {
      $person = Person::find($key);
      $temp['person'] =  $person->first_name . ' ' . $person->last_name;
      $temp['ledgers'] = $value;
      $rescollect->push($temp);
      unset($temp);
    }
    return response()->json($rescollect, 200);
  }
}
