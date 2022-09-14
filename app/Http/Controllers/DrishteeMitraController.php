<?php

namespace App\Http\Controllers;

use App\DrishteeMitra;
use Illuminate\Http\Request;
use Hash;
use Config;
use JWTAuth;
use App\DMProfile;
use Validator;
use File;
use Storage;
use App\Person;
use App\DMGeography;
use Carbon\Carbon;
use App\Ledger;
use App\LedgerTransaction;
use App\Mail\DmCreateMail;
use Mail;
use App\Mail\DmUpdateMail;
use App\Mail\DmDeactivateMail;
use App\User;
use App\Notification;
use App\State;
use App\Geography;
use App\Product;
use App\Barter;
use App\PersonService;
use App\Service;
use Rap2hpoutre\FastExcel\FastExcel;
use App\PersonProduct;
use Log;
class DrishteeMitraController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct()
    {
    Config::set('jwt.user', DrishteeMitra::class);
    Config::set('auth.providers', ['users' => [
            'driver' => 'eloquent',
            'model' => DrishteeMitra::class,
        ]]);
    }

    public function dashboard(Request $request ){
        // $data = [];
        $validation = Validator::make($request->all(),[
            'startdate' => 'sometimes|date_format:Y/m/d|before:today',
            'enddate' => 'sometimes|date_format:Y/m/d|after:startdate|before_or_equal:today',
        ]);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,400);
        }
        // if(!$request->startdate || !$request->enddate){
        //     $startdate = Carbon::parse(new Carbon('first day of last month'))->format('Y/m/d');
        //     $enddate = Carbon::parse(new Carbon('last day of last month'))->format('Y/m/d');
        // }else{
        //     $startdate = Carbon::parse($request->startdate)->format('Y/m/d');
        //     $enddate = Carbon::parse($request->enddate)->format('Y/m/d');
        // }
        $startdate = null;
        $enddate= null;
        if($request['startdate'] && $request['enddate']) {
            $startdate = Carbon::parse($request->startdate)->format('Y/m/d');
            $enddate = Carbon::parse($request->enddate)->format('Y/m/d');
        }
        $geography = Geography::count();
        $dm = DrishteeMitra::all();
        //$persons = Person::all();
        $personCount = Person::count();
        $products = Product::count();
        $pService = PersonService::count();
        if($startdate && $enddate) {
            $batters = Barter::where('created_at','>=',$startdate)->where('created_at','<=',$enddate)->get();
            $battersCount = count($batters);
        }
        else {
            $batters = Barter::all();
            $battersCount = Barter::count();

        }
        
        $batterslp = 0;
        $bGID = [];
        foreach ($batters as $key) {
            $batterslp = $key->barter_total_lp_offered + $batterslp;
            if(!in_array($key->geography_id, $bGID)){
                array_push($bGID, $key->geography_id);
            }
        }


        $tejasProduct = Product::where('is_gold_product',true)->count();
        $personWithNoProduct = PersonProduct::distinct('person_id')->count('person_id');
       
        // foreach ($persons as $key) {
        //     if(count($key->personProducts) == 0){
        //         $personWithNoProduct++;
        //     }
        // }
        
        
        $avgProduct = round($products/$personCount,1);

        $dmWithNoPerson = 0;
        foreach ($dm as $key) {
            if(count($key->personAddedBy)){
                $dmWithNoPerson++;
            }
        }
        $avgDm = round($personCount/count($dm),1);
        $avgServices = round($personCount/$pService,1);
        return response()->json(
            ['dm'=>count($dm),
            'geography'=>$geography,
            'persons'=> $personCount,
            'products'=>$products,
            'batters'=> $battersCount,
            'batterslp'=>$batterslp,
            'batterGC'=> count($bGID),
            'tejasProduct'=>$tejasProduct,
            'avgProduct'=>$avgProduct,
            'personsWithNoProduct'=>$personCount - $personWithNoProduct,
            'dmWithNoPerson'=>$dmWithNoPerson ,
            'avgDm'=>$avgDm,
            'avgServices'=>$avgServices],200);
    }

    public function dashboardTejasProductsExport(){
        $data = [];
        $products = Product::where('is_gold_product',true)->get();

        foreach ($products as $key) {
            array_push($data,["Name"=>$key->name, "Default Livehood Points"=>$key->default_livehood_points,
                "Category Name"=>$key->productCategory->name,"MRP"=>$key->mrp,'Added At'=> $key->created_at]);
        }
        $file = Carbon::now()->format('YmdHis').'dashboardTejasProducts.xlsx';
        $filepath = (new FastExcel($data))->export(storage_path().'/'.$file);
        $url = "api/productsexport/".$file;
        return response()->json(url($url),200);
    }

    public function dashboardAvgProductsExport(){
        $data = [];
        $products = Product::all();
        foreach ($products as $key) {
            array_push($data,["Product Name"=>$key->name,"Person Count"=>count($key->personProduct)]);
        }
        $file = Carbon::now()->format('YmdHis').'dashboardAvgProducts.xlsx';
        $filepath = (new FastExcel($data))->export(storage_path().'/'.$file);
        $url = "api/productsexport/".$file;
        return response()->json(url($url),200);
    }

    public function dashboardAvgServicesExport(){
        $data = [];
        $services = Service::all();
        foreach ($services as $key) {
            array_push($data,["Service Name"=>$key->name,"Person Count"=>count($key->personService)]);
        }
        $file = Carbon::now()->format('YmdHis').'dashboardAvgServices.xlsx';
        $filepath = (new FastExcel($data))->export(storage_path().'/'.$file);
        $url = "api/productsexport/".$file;
        return response()->json(url($url),200);
    }

    public function dashboardAvgPeopleMitraExport(){
        $data = [];
        $dm = DrishteeMitra::all();
        foreach ($dm as $key) {
            foreach ($key->personAddedBy as $person) {
               array_push($data,["Mitra Name"=>$key->first_name.' '.$key->last_name,"Person Name"=> $person->first_name.' '.$person->last_name,"State"=> $key->dmGeography->geography->state,
                                    "District"=> $key->dmGeography->geography->district,
                                    "Geography"=>$key->dmGeography->geography->name,]);
            }
            
        }
        $file = Carbon::now()->format('YmdHis').'dashboardAvgPeopleMitra.xlsx';
        $filepath = (new FastExcel($data))->export(storage_path().'/'.$file);
        $url = "api/productsexport/".$file;
        return response()->json(url($url),200);
    }

    public function dashboardMitraNoPeople(){
        $data = [];
        $dm = DrishteeMitra::all();
        foreach ($dm as $key) {
            if(count($key->personAddedBy) == 0){
                array_push($data,["Mitra Name"=>$key->first_name.' '.$key->last_name]);
            }
        }
        $file = Carbon::now()->format('YmdHis').'dashboardMitraNoPeople.xlsx';
        $filepath = (new FastExcel($data))->export(storage_path().'/'.$file);
        $url = "api/productsexport/".$file;
        return response()->json(url($url),200);
    }

    public function dashboardPeopleWithNoProduct(){
        $data = [];
        $person = Person::all();
        foreach ($person as $key) {
            if(count($key->personProducts) == 0){
                array_push($data,["Person Name"=>$key->first_name.' '.$key->last_name,
                                    "State"=> $key->geographies->state,
                                    "District"=> $key->geographies->district,
                                    "Geography"=>$key->geographies->name,
                                    "Added By"=> ($key->dm)? $key->dm->first_name.' '.$key->last_name : null]);
            }
        }
        $file = Carbon::now()->format('YmdHis').'dashboardPeopleWithNoProduct.xlsx';
        $filepath = (new FastExcel($data))->export(storage_path().'/'.$file);
        $url = "api/productsexport/".$file;
        return response()->json(url($url),200);
    }


    public function reportServicesExport(){
        $data = [];
        $services = Service::all();
        foreach ($services as $key) {
            array_push($data, ["Name"=>$key->name,"Category Name"=>$key->serviceCategory->name,
                "Default Livehood Points"=>$key->default_livelihood_points,"Added At"=>$key->created_at]);
        }
        $file = Carbon::now()->format('YmdHis').'reportServices.xlsx';
        $filepath = (new FastExcel($data))->export(storage_path().'/'.$file);
        $url = "api/productsexport/".$file;
        return response()->json(url($url),200);
    }

    public function reportProductsExport(){
        $data = [];
        $products = Product::all();
        foreach ($products as $key) {
            array_push($data,[
                "Name"=>$key->name, 
                "Default Livehood Points"=>$key->default_livehood_points,
                "Calculate Raw Material Cost"=>$key->calc_raw_material_cost,
                "Calculate Hours Worked"=>$key->calc_hours_worked,
                "Calculate Wage Applicable"=>$key->calc_wage_applicable,
                "Calculate Margin Applicable"=>$key->calc_margin_applicable,
                "Category Name"=>($key->productCategory) ? $key->productCategory->name : null,
                "MRP"=>$key->mrp,
                'Is Gold Product'=>$key->is_gold_product,
                'Availability'=>$key->availability,
                'Added At'=> $key->created_at]);
        }
        $file = Carbon::now()->format('YmdHis').'reportProducts.xlsx';
        $filepath = (new FastExcel($data))->export(storage_path().'/'.$file);
        $url = "api/productsexport/".$file;
        return response()->json(url($url),200);
    }

    public function reportGeographyExport(){
        $data = [];
        $geography = Geography::all();
        // return response()->json($geography->userGeographies,200);
        
        foreach ($geography as $key) {
            $user = null;
            foreach ($key->userGeographies as $ug) {
                if($ug->user && !$ug->user->is_super_admin) $user = $ug->user->first_name.' '.$ug->user->last_name." , ".$user;
            }


            array_push($data, ['Name'=> $key->name,
                                'State'=> $key->state,
                                'District'=> $key->district,
                                'Type'=> $key->type,
                                'Admins'=> $user,
                                'Created At'=> Carbon::parse($key->created_at)->format('Y/m/d')]);
        }

        $file = Carbon::now()->format('YmdHis').'reportGeographyExport.xlsx';
        $filepath = (new FastExcel($data))->export(storage_path().'/'.$file);
        $url = "api/productsexport/".$file;
        return response()->json(url($url),200);
    }


    public function reportGeographyWiseLedgerExport(){
        $data = [];
        $geography = Geography::all();
        $ladger = 0;
        foreach ($geography as $Gkey) {
            foreach ($Gkey->personProduct as $key) {
                if($key->person && $key->person->ledgers){
                   $ladger = $ladger +  $key->person->ledgers[0]->balance;
                }
            }
            array_push($data,["Geography Name"=>$Gkey->name,"Ladger"=>$ladger]);
            $ladger = 0;
        }
        $file = Carbon::now()->format('YmdHis').'reportGeographyWiseLedgerExport.xlsx';
        $filepath = (new FastExcel($data))->export(storage_path().'/'.$file);
        $url = "api/productsexport/".$file;
        return response()->json(url($url),200);
    }

    public function reportLedgerExport(Request $request){
        $validation = Validator::make($request->all(),[
            'startDate' => 'sometimes|date_format:Y/m/d|before:today',
            'endDate' => 'sometimes|date_format:Y/m/d|after:startDate',
        ]);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,400);
        }
        $data =[];
        
        if($request->startdate && $request->enddate){
            $request->startDate = $request->startdate;
            $request->endDate = $request->enddate; 
        }

        $startdate = Carbon::parse($request->startDate)->format('Y/m/d');
        $enddate = Carbon::parse($request->endDate)->format('Y/m/d');
        if($request->people){
            $person = Person::find($request->people);
            $ledger = Ledger::find($person->ledger_id);
            $ledgerTransaction = LedgerTransaction::where('ledger_id',$ledger->id)->where('created_at','>=',$startdate)->where('created_at','<=',$enddate)->get();
            foreach ($ledgerTransaction as $key) {
                array_push($data,
                    ["Type"=>"People",
                    "Entity"=>$person->first_name.' '.$person->last_name,
                    "DateTime"=>Carbon::parse($key->created_at)->format('Y/m/d'),
                    'Livelihood Points (LP)'=>$key->amount,
                    "Credit/Debit"=>$key->transaction_type,
                    'Balance'=>$key->balance_after_transaction]);
            }
            array_push($data,["Type"=>"People",
                    "Entity"=>'',
                    "DateTime"=>'','Livelihood Points (LP)'=>0,
                    "Credit/Debit"=>'','Balance'=>0]);
            $file = Carbon::now()->format('YmdHis').'reportledgerPeopleExport.xlsx';
            $filepath = (new FastExcel($data))->export(storage_path().'/'.$file);
            $url = "api/productsexport/".$file;
            return response()->json(url($url),200);

        }else if($request->dm){
            $dm = DrishteeMitra::find($request->dm);
            $ledger = Ledger::find($dm->ledger_id);
            $ledgerTransaction = LedgerTransaction::where('ledger_id',$ledger->id)->where('created_at','>=',$startdate)->where('created_at','<=',$enddate)->get();

            foreach ($ledgerTransaction as $key) {
                array_push($data,["Type"=>"Mitra",
                    "Entity"=>$dm->first_name.' '.$dm->last_name,
                    "DateTime"=>Carbon::parse($key->created_at)->format('Y/m/d'),
                    'Livelihood Points (LP)'=>$key->amount,
                    "Credit/Debit"=>$key->transaction_type,
                    'Balance'=>$key->balance_after_transaction]);
            }
            array_push($data,["Type"=>"Mitra","Entity"=>'',
                    "DateTime"=>'','Livelihood Points (LP)'=>0,"Credit/Debit"=>'','Balance'=>0]);
            $file = Carbon::now()->format('YmdHis').'reportledgerMitraExport.xlsx';
            $filepath = (new FastExcel($data))->export(storage_path().'/'.$file);
            $url = "api/productsexport/".$file;
            return response()->json(url($url),200);

        }else if($request->admin){
            $user = User::find($request->admin);
            $ledger = Ledger::find($user->ledger_id);
            $ledgerTransaction = LedgerTransaction::where('ledger_id',$ledger->id)->where('created_at','>=',$startdate)->where('created_at','<=',$enddate)->get();

            foreach ($ledgerTransaction as $key) {
                array_push($data,["Type"=>"Admin",
                    "Entity"=>$user->first_name.' '.$user->last_name,
                    "DateTime"=>Carbon::parse($key->created_at)->format('Y/m/d'),
                    'Livelihood Points (LP)'=>$key->amount,"Credit/Debit"=>$key->transaction_type,'Balance'=>$key->balance_after_transaction]);
            }
            array_push($data,["Type"=>"Admin","Entity"=>'',
                    "DateTime"=>'','Livelihood Points (LP)'=>0,"Credit/Debit"=>'','Balance'=>0]);
            $file = Carbon::now()->format('YmdHis').'reportledgerAdminExport.xlsx';
            $filepath = (new FastExcel($data))->export(storage_path().'/'.$file);
            $url = "api/productsexport/".$file;
            return response()->json(url($url),200);

        }else{
           return response()->json([],200);
       }
    }

    public function reportBarterExport(Request $request){
        $validation = Validator::make($request->all(),[
            'startDate' => 'sometimes|date_format:Y/m/d|before:today',
            'endDate' => 'sometimes|date_format:Y/m/d|after:startDate',
        ]);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,400);
        }
        $data =[];
        
        $startdate = Carbon::parse($request->startDate)->format('Y/m/d');
        $enddate = Carbon::parse($request->endDate)->format('Y/m/d');

        $barters = Barter::where('created_at','>=',$startdate)->where('created_at','<=',$enddate)->get();
        foreach ($barters as $key) {
            $products = [];
            $services = [];
            foreach ($key->barterHaveProducts as $pro) {
                array_push($data,["Id"=>$key->id,'DateTime'=>$key->created_at,
                    "Mitra"=>(($key->drisheeMitras) ? ($key->drisheeMitras->first_name.' '.$key->drisheeMitras->last_name): ""),
                    'Geography'=>$key->geography->name,
                    "State"=> (($key->person) ?  ($key->person->state->name): ""),
                    "Person"=> (($key->person) ? ( $key->person->first_name.' '.$key->person->last_name): ""),
                    "Product Name"=>$pro->personProduct->product->name,
                    "Product LP"=>$pro->product_lp,
                    "Product Amount"=>$pro->quantity,
                    "Service Name"=>"",
                    "Service LP"=>"",
                    "Service Days"=> "",
                    "LP"=>"",
                    "Create At" => Carbon::parse($key->created_at)->format('Y/m/d'),
                    "Complete At"=> Carbon::parse($key->updated_at)->format('Y/m/d')]);
            }
            foreach ($key->barterHaveServices as $ser) {
                array_push($data,["Id"=>$key->id,'DateTime'=>$key->created_at,
                    "Mitra"=>(($key->drisheeMitras) ? ($key->drisheeMitras->first_name.' '.$key->drisheeMitras->last_name): ""),
                    'Geography'=>$key->geography->name,
                    "State"=> (($key->person) ?  ($key->person->state->name): ""),
                    "Person"=> (($key->person) ? ( $key->person->first_name.' '.$key->person->last_name): ""),
                    "Product Name"=>"",
                    "Product LP"=>"",
                    "Product Amount"=>"",
                    "Service Name"=>$ser->personService->service->name,
                    "Service LP"=>$ser->service_lp,
                    "Service Days"=> $ser->no_of_days,
                    "LP"=>"",
                    "Create At" => Carbon::parse($key->created_at)->format('Y/m/d'),
                    "Complete At"=> Carbon::parse($key->updated_at)->format('Y/m/d')]);   
            }
            foreach ($key->barterHaveLp as $blp) {
                array_push($data,["Id"=>$key->id,'DateTime'=>$key->created_at,
                    "Mitra"=>(($key->drisheeMitras) ? ($key->drisheeMitras->first_name.' '.$key->drisheeMitras->last_name): ""),
                    'Geography'=>$key->geography->name,
                    "State"=> (($key->person) ?  ($key->person->state->name): ""),
                    "Person"=> (($key->person) ? ( $key->person->first_name.' '.$key->person->last_name): ""),
                    "Product Name"=>"",
                    "Product LP"=>"",
                    "Product Amount"=>"",
                    "Service Name"=>"",
                    "Service LP"=>"",
                    "Service Days"=> "",
                    "LP"=>$blp->lp,
                    "Create At" => Carbon::parse($key->created_at)->format('Y/m/d'),
                    "Complete At"=> Carbon::parse($key->updated_at)->format('Y/m/d')]);   
            }
        }


        // return $data;
        $file = Carbon::now()->format('YmdHis').'reportBarterExport.xlsx';
            $filepath = (new FastExcel($data))->export(storage_path().'/'.$file);
            $url = "api/productsexport/".$file;
            return response()->json(url($url),200);
    }

    /**
    * @param  \Illuminate\Http\Request  $request
    * @return \App\DrishteeMitra $dm with token
    * do filter drishtee object name, email, mobile, geography
    */
    public function filterDrishtee(Request $request){
        //return $request;
        $geography_ids = $request['geography_ids'];
        $dms = DrishteeMitra::select('drishtree_mitras.*','d_m_profiles.*','d_m_geographies.geography_id')
            ->whereHas('dmGeography',function($query) use($geography_ids) {
                $query->whereIn('geography_id', $geography_ids);
            })
            ->with('dmDevice')->withCount('personAddedBy')
            ->join('d_m_profiles', 'drishtree_mitras.id', '=', 'd_m_profiles.dm_id')
            ->join('d_m_geographies', 'drishtree_mitras.id', '=', 'd_m_geographies.dm_id')   
            ->orderBy('drishtree_mitras.first_name');
        //return $dms = DrishteeMitra::with('dmProfile','dmDevice','dmGeography.geography','personAddedBy')->get()->take(10);


        if(isset($request['filters']['name']) && !empty($request['filters']['name'])) 
        {
            $dms->where('first_name','like','%'.$request['filters']['name'].'%')
                 ->orWhere('last_name','like','%'.$request['filters']['name'].'%');
        }
        if(isset($request['filters']['email']) && !empty($request['filters']['email'])) {
            $dms->where('email','like','%'.$request['filters']['email'].'%');

        }
        if(isset($request['filters']['mobile']) && !empty($request['filters']['mobile'])) {
            $dms->where('mobile','like','%'.$request['filters']['mobile'].'%');

        }
        if(isset($request['filters']['geography_id']) && !empty($request['filters']['geography_id'])) {
            $geography_id = $request['filters']['geography_id'];
            $dms->whereHas('dmGeography',function($query) use($geography_id) {
                $query->where('geography_id', $geography_id);
            });
        }
        $dms = $dms->get();
        foreach ($dms as $value) {
            $value->geography_name = Geography::where('id','=',$value->geography_id)->first()->name;
        }

        
        return response()->json($dms,200);  
    }

    public function index()
    {
       return response()->json(DrishteeMitra::orderBy('first_name')->get()->load('dmProfile','dmDevice','dmGeography','personAddedBy'),200);
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
    public function store(Request  $request){
        
        $validation = Validator::make($request->all(),[
             'first_name'                 => 'required',
             'password'                   => 'required',
             'last_name'                  => 'required',
             'middle_name'                => 'sometimes',
             'email'                      => 'required|email|unique:drishtree_mitras,email',
             'mobile'                     => 'required|unique:drishtree_mitras,mobile',
             'last_password_change'       => 'sometimes'  ,
             'ledger_id'                  => 'sometimes',
             'otp'                        => 'sometimes',
             'user_type'                  => 'sometimes'  ,
             'is_mobile_onboarded'        => 'sometimes',
             'status'                     => 'sometimes',
             'added_by'                   => 'required',
             'geography_id'               => 'required',
             'geography_type'             => 'required',
             'state_id'                   => 'required'  
        ]);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,400);
        }

        $data['first_name']= $request['first_name'];
        if(isset($request['middle_name'])) {
        $data['middle_name'] = $request['middle_name'];
        }
        else {
            $data['middle_name'] = '';
        }
        $data['last_name'] = $request['last_name'];
        $data['email'] = $request['email'];
        $data['mobile'] = $request['mobile'];
        $data['geography_id'] = $request['geography_id'];
        $data['geography_type'] = $request['geography_type'];
        // $data['dm_id'] = $request['added_by'];
        $data['added_on'] = $request['added_on'] = Carbon::now()->toDateTimeString();
        $data['state_id'] = $request['state_id'];
        $person = Person::create($data);
        $person->createPersonTransaction('Success',0);
        $person_ledger = Ledger::where('ledger_id',$person->id)->first();
        $person->ledger_id = $person_ledger->id;
        $person->save(); 
        $request['person_id'] = $person->id; 
        
        $dm = $request->only(['first_name','middle_name','last_name','email','mobile']);
        if(isset($request['password'])) {
            $request['password'] = Hash::make($request->password);
            $dm = $request->only(['first_name','middle_name','last_name','password','email','mobile','person_id','added_by','added_on','state_id']);
        }
        $dm = DrishteeMitra::create($dm);
        Mail::to($dm->email)->send(new DmCreateMail($dm));
        sendSMS('Welcome to Drishtee Foundation. Your Drishtee Mitra account has been created. Download the app http://bit.ly/MiriMarketBarter.',$dm->mobile);
        $dm->createDmTransaction('Success',0);

        $ledger = Ledger::where('ledger_id',$dm->id)->first();
        $dm->ledger_id = $ledger->id;
        $dm->save(); 
        $dmg['dm_id'] = $dm->id;
        $dmg['added_by'] = $request['added_by'];
        $dmg['added_on']= $data['added_on'];
        $dmg['geography_id'] = $request['geography_id'];
        $dmg['geography_type'] = $request['geography_type'];
        $dmg = DMGeography::create($dmg);
        $ndata = array();
        $dp = $dm->dmProfile()->create($ndata);
        $person->dm_id = $dm->id;
        $person->save();
        return response()->json($dm->load('dmProfile'), 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\DrishtreeMitra  $drishtreeMitra
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $dm = DrishteeMitra::find($id);
        $dm->geography_id = $dm->dmGeography->geography_id;
        
        return response()->json($dm->load('DMProfile','dmDevice','dmGeography','personProduct.product.productCategory','personProduct.unit','ledgers.ladgerTransaction','ledger'),200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\DrishtreeMitra  $drishtreeMitra
     * @return \Illuminate\Http\Response
     */
    public function edit(DrishteeMitra $drishtreeMitra)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\DrishtreeMitra  $drishtreeMitra
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id){
        $validation = Validator::make($request->all(),[
             'first_name'                 => 'required',
             'middle_name'                => 'sometimes',
             'last_name'                  => 'required',
             'mobile'                     => 'required|unique:drishtree_mitras,mobile,'.$id,
             'geography_id'               => 'required'
        ]);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,400);
        }
        $dm = DrishteeMitra::find($id);
        if($dm) {
            $dmgeo = DMGeography::where('dm_id',$dm->id)->first();
            $dmgeo->update($request->only('geography_id'));
            $dm->update($request->all());

            $persons = Person::where('dm_id',$dm->id)->get();
            foreach ($persons as $p) {
                foreach ($p->personProducts as $pp) {
                    $pp->update($request->only('geography_id'));
                }
                foreach ($p->personServices as $ps) {
                    $ps->update($request->only('geography_id'));
                }
                $p->update($request->only('geography_id'));
            }

            Mail::to($dm->email)->send(new DmUpdateMail($dm));
            sendSMS('Your Drishtee Mitra profile has been updated successfully.',$dm->mobile);
            return response()->json($dm->load('dmGeography'),201);
        }
        return response()->json(['error'=>'DM Not Found'],404);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\DrishtreeMitra  $drishtreeMitra
     * @return \Illuminate\Http\Response
     */
    public function destroy(DrishteeMitra $drishtreeMitra)
    {
        //
    }

    /**
    * @param  \Illuminate\Http\Request  $request
    * @return \App\DrishteeMitra $dm with token
    * do generate token related to mobile and otp
    */
    public function login(Request $request) {
         $validation = Validator::make($request->all(),[
             'mobile'               => 'required',
             'otp'                  => 'required',
             'device_token'         => 'sometimes'
        ]);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,400);
        }
        $user = DrishteeMitra::authenticateUser($request['mobile'],$request['otp']);
       // $customClaims = ['model_type' => 'users'];
       // Config::set('auth.providers.users.model', \App\DrishteeMitra::class);
       // Config::set('jwt.user', "App\DrishteeMitra");

        $customClaims = ['model_type' => 'drishtee_mitra'];
        $token = JWTAuth::fromUser($user,$customClaims);
        $result['token'] = 'Bearer ' . $token;
        
        // if($request->device_token && $request->device_token != null){
        //     $user->device_token = $request->device_token;
        //     $user->save();
        // }
        
        // $noti = new NotificationController;

        // $data['title'] = "title";
        // $data['body'] = "body";
        // $data['id'] = 2;
        // $data['type'] = "DM";
        // $result['data'] = $noti->toSingleDevice($user,$data);

        $result['user'] = $user->load('dmGeography','dmProfile','dmDevice');

        return $result;
    }
    
    /**
    * 
    * @return true / false
    * do delete login jwt token
    */
    public function logout() {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(true,200); 
    }


    public function testToken() {
        if (!$user = JWTAuth::parseToken()->authenticate()) {
            throw new \App\Repositories\Exceptions\ModelNotFound;
        }
        return $user->load('role.permission');
    }

    /**
    * 
    * @return JWT $token
    * do refresh old jwt token
    */
    public function refreshToken() {
        $token = JWTAuth::getToken();
        $token = JWTAuth::refresh($token);
        $tk= 'Bearer '.$token;
       // $ex = JWTAuth::parseToken()->getPayload()->get('exp');
        return response()->json(['token'=> $tk],200);
    }

    /**
    * @param  \Illuminate\Http\Request  $request
    * @return true / false
    * do generate otp and save it drishteemitra table related to dm mobile
    */
    public function checkMobile(Request $request){
        $dm = DrishteeMitra::where('mobile',$request->mobile)->first();
        if($dm) {
            $otp = rand(1000,9999);
            if($dm->id == 3) {
            $dm->otp = 1111;
            $dm->save();     
            }
            else {
            $dm->otp = $otp;
            $dm->save();
            sendSMS('Your Drishtee Mitra login OTP is '.$dm->otp,$dm->mobile);

            }
            return response()->json(true,200);
        }
        return response()->json(false,200);

    }

    /**
    * @param  \Illuminate\Http\Request  $request
    * @return true / false
    * do generate otp and save it drishteemitra table related to dm mobile
    */
    public function resendOtp(Request $request){
        $dm = DrishteeMitra::where('mobile',$request->mobile)->first();
        if($dm) {
            $otp = rand(1000,9999);
            if($dm->id == 3) {
            $dm->otp = 1111;
            $dm->save();     
            }
            else {
            $dm->otp = $otp;
            $dm->save();
            sendSMS('Your Drishtee Mitra login OTP is '.$dm->otp,$dm->mobile);

            }
            return response()->json(true,200);
        }
        return response()->json(false,200);

    }

    /**
    * @param  \Illuminate\Http\Request  $request
    * @param  \App\DrishteeMitra $id
    * @return \App\DrishteeMitra $dm
    * do store dm profile image
    */
    public function uploadImage(Request $request,$id){
        // dd($request['file']);
       //$this->authorize('create',StaffBasicDetails::class);
       $validation = Validator::make($request->all(),[
            'file' => 'required|file|mimes: jpg,jpeg,png,bmp'
        ]);
        if($validation->fails()){
            $errors = $validation->errors();
            return response()->json($errors, 400);
        }
       $dm = DrishteeMitra::find($id);
       $dp = DMProfile::where('dm_id',$dm->id)->first();
       // dd($dp);
       $file = $request['file']->getClientOriginalName();
       $extension = $request['file']->getClientOriginalExtension(); 
       $filename = pathinfo($file, PATHINFO_FILENAME);
       $originalName = $filename.'.'.$extension; 
       $cover = $request->file('file');
       Storage::disk('public')->put($originalName,File::get($cover));
       $request['photo_name'] = $originalName;
       $request['photo_path'] = Storage::disk('public')->url($originalName);
       $dp->update($request->only(['photo_name','photo_path']));
       return response()->json($dm->load('DMProfile','state'),201); 
    }

    /**
    * @param  \App\DrishteeMitra $id
    * @return \App\DrishteeMitra $dm
    */
    public function onBoardComplete($id){
        $dm = DrishteeMitra::find($id);
        if($dm) {
            $dm->is_mobile_onboarded = true;
            $dm->save();
            return response()->json($dm,200);
        }
        return response()->json(['error'=>'DM Not Found'],404);
    }

    /**
    * @param  \Illuminate\Http\Request  $request
    * @param  \App\DrishteeMitra $id
    * @return \App\DMDevice $dd
    * do store device properties of dm account login
    */
    public function saveDeviceDetail(Request $request,$id){
        $request['device_properties'] = json_encode($request['device_properties']);
        // dd($request->all()); 
        $dm = DrishteeMitra::find($id);
        if($dm) {
            $dd = $dm->dmDevice()->create($request->all());
            return response()->json($dd,201);    
        }
        return response()->json(['error'=>'DM Not Found'],404);
    }

    /**
    * @param  \Illuminate\Http\Request  $request
    * @param  \App\DrishteeMitra $transfer_dm_id
    * @param  \App\DrishteeMitra $dm_id
    * @return \App\DrishteeMitra $dm
    * do check ledger balance is 0 then transfer account then change status of $dm
    */
    public function statusChange(Request $request, $dm_id,$transfer_dm_id){
        $validation = Validator::make($request->all(),[
             'status'                 => 'required|boolean',
        ]);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,400);
        }

        $dm = DrishteeMitra::find($transfer_dm_id);
        $ledger =  Ledger::find($dm->ledger_id);
        if($request->status == false){
            if($ledger->balance == 0){
                if($this->dmTransfer($dm_id,$transfer_dm_id)){
                    $dm_buyRequests = $dm->tejasProductBuyRequests;
                    $dm_sellRequests = $dm->tejasProductSellRequests;
                    foreach ($dm_buyRequests as $buyRequest) {
                        if($buyRequest->status == "Open" || $buyRequest->status == "open"){
                            $buyRequest->status = "Cancel";
                            $buyRequest->save();
                        }
                    }

                    foreach ($dm_sellRequests as $sellRequest) {
                        if($sellRequest->status == "Open" || $sellRequest->status == "open"){
                            $sellRequest->status = "Cancel";
                            $sellRequest->save();
                        }
                    }
                    $users  = User::where('is_super_admin',true)->get();
                    foreach ($users as $user) {
                        Mail::to($user->email)->send(new DmDeactivateMail($user));
                    }
                    $dm->status = $request->status;
                    $dm->save();
                    
                    sendSMS('Your Drishtee Mitra profile has been deactivated on the Miri Market Barter app.',$dm->mobile);
                    return response()->json($dm,200);

                }
                return response()->json(['error'=>"DM Account Not Transfer."],200);
            }
            return response()->json(['error'=>"DM Ledger Balance Not 0."],200);
        }
        $dm->status = $request->status;
        $dm->save();

        return response()->json($dm,200);
    }   

    /**
    * @param  \App\DrishteeMitra $transfer_dm_id
    * @param  \App\DrishteeMitra $dm_id
    * @return true / false
    * do transer all person, barter, dispute to $transfer_dm_id
    */
    public function dmTransfer($dm_id,$transfer_dm_id)
    {
        $dm = DrishteeMitra::find($dm_id);
        $persons = $dm->personAddedBy;
        foreach ($persons as $person) {
            $person->dm_id = $transfer_dm_id;
            $person->save();
            sendSMS('Your Drishtee Mitra profile has been successfully transferred to another DM.',$person->mobile);  
        }
        $barters = $dm->addedBarters;

        foreach ($barters as $barter) {
            if($barter->status == 'Open') {
                $barter->added_by_dm_id = $transfer_dm_id;
                $barter->save();
            }
        }
        $disputes = $dm->dispute;
        foreach ($disputes as $dis) {
            $dis->added_by = $transfer_dm_id;
            $dis->save();
        }

        return response()->json(true,200);
    }

    /**
    * @param  \Illuminate\Http\Request  $request
    * @param  \App\DrishteeMitra $dm_id
    * @return \App\LedgerTransaction $ledgerTransaction
    * do find out all transaction related to $dm_id and in between start and end date 
    */
    public function ledgerfilter(Request $request, $dm_id){
        $validation = Validator::make($request->all(),[
            'startdate' => 'required|date_format:Y/m/d|before:today',
            'enddate' => 'required|date_format:Y/m/d|after:startdate|before_or_equal:today',
        ]);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,400);
        }

        $startdate = Carbon::parse($request->startdate)->format('Y/m/d');
        $enddate = Carbon::parse($request->enddate)->format('Y/m/d');
        $dm = DrishteeMitra::find($dm_id);
        $ledger = Ledger::find($dm->ledger_id);

        $transaction = LedgerTransaction::where('ledger_id',$ledger->id)->where('created_at','>=',$startdate)->where('created_at','<=',$enddate)->get();
        return response()->json($transaction,200);
    }

    /**
    * @param \App\DrishteeMitra $dm_id
    * @return \App\LedgerTransaction $ledgerTransaction
    * do find out all transaction related to $dm_id
    */
    public function getDMLedgerTransaction($dm_id){
        $dm = DrishteeMitra::find($dm_id);
        $ledger = Ledger::find($dm->ledger_id);

        $startdate = Carbon::now()->subDays(30)->format('Y/m/d');
        $enddate = Carbon::tomorrow()->format('Y/m/d');

        $transaction = LedgerTransaction::where('ledger_id',$ledger->id)->where('created_at','>=',$startdate)->where('created_at','<=',$enddate)->get();
        return response()->json($transaction,200);

        // $ledgerTransaction = LedgerTransaction::where('ledger_id',$ledger->id)->get();
        // return response()->json($ledgerTransaction,200);        
    }

    public function exportDMLedgerTransaction(Request $request,$dm_id){
        $validation = Validator::make($request->all(),[
            'startdate' => 'sometimes|date_format:Y/m/d|before:today',
            'enddate' => 'sometimes|date_format:Y/m/d|after:startdate|before_or_equal:today',
        ]);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,400);
        }
        $startdate = null;
        $enddate = null;
        $data = [];
        
        $dm = DrishteeMitra::find($dm_id);
        $ledger = Ledger::find($dm->ledger_id);

        if($request->startdate && $request->enddate){
            $startdate = Carbon::parse($request->startdate)->format('Y/m/d');
            $enddate = Carbon::parse($request->enddate)->format('Y/m/d');
        }else{
            $startdate = Carbon::now()->subDays(30)->format('Y/m/d');
            $enddate = Carbon::tomorrow()->format('Y/m/d');    
        }
        $ledgerTransaction = LedgerTransaction::where('ledger_id',$ledger->id)->where('created_at','>=',$startdate)->where('created_at','<=',$enddate)->get();

        foreach ($ledgerTransaction as $key) {
            array_push($data,["Type"=>"Mitra",
                "Entity"=>$dm->first_name.' '.$dm->last_name,
                "DateTime"=>Carbon::parse($key->created_at)->format('Y/m/d'),
                'Livelihood Points (LP)'=>$key->amount,
                "Credit/Debit"=>$key->transaction_type,
                'Balance'=>$key->balance_after_transaction]);
        }
        array_push($data,["Type"=>"Mitra","Entity"=>'',
                "DateTime"=>'','Livelihood Points (LP)'=>'',"Credit/Debit"=>'','Balance'=>'']);
        $file = Carbon::now()->format('YmdHis').'reportledgerMitraExport.xlsx';
        $filepath = (new FastExcel($data))->export(storage_path().'/'.$file);
        $url = "api/productsexport/".$file;
        return response()->json(url($url),200);     
    }
    public function createDrishteeMitra(Request $request)
    {
        $validation = Validator::make($request->all(),[
             'first_name'                 => 'required',
             'middle_name'                => 'sometimes',
             'last_name'                  => 'required',
             'email'                      => 'required|email|unique:drishtree_mitras,email',
             'mobile'                     => 'required|unique:drishtree_mitras,mobile',
             'geography_name'             => 'required',
             'state_name'                 => 'required',
             'ID'                         => 'required' 
        ]);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,400);
        }
        Log::info("heee".$request);
        $geography = Geography::where('name',$request['geography_name'])->first();
        $state = State::where('name',$request['state_name'])->first();
        $data['first_name']= $request['first_name'];
        if(isset($request['middle_name'])) {
        $data['middle_name'] = $request['middle_name'];
        }
        else {
            $data['middle_name'] = '';
        }
        $data['last_name'] = $request['last_name'];

        $data['email'] = $request['email'];
        $data['mobile'] = $request['mobile'];
        $data['geography_id'] = $geography->id;
        $data['geography_type'] = $geography->type;
        // $data['dm_id'] = $request['added_by'];
        $data['added_on'] = $request['added_on'] = Carbon::now()->toDateTimeString();
        $data['state_id'] = $state->id;
        $person = Person::create($data);
        $person->createPersonTransaction('Success',0);
        $person_ledger = Ledger::where('ledger_id',$person->id)->first();
        $person->ledger_id = $person_ledger->id;
        $person->save(); 
        $request['person_id'] = $person->id;
        $user = User::where('is_super_admin',true)->first(); 
        $request['added_by'] = $user->id;
        $dm = $request->only(['first_name','middle_name','last_name','email','mobile']);
        $request['password'] = 123456;
        $request['state_id'] = $state->id;
        $request['geography_id'] = $geography->id;
        $request['geography_type'] = $geography->type;
        $request['remote_id'] =  $request['ID'];
        if(isset($request['password'])) {
            // $password = '123456';
            $request['password'] = Hash::make($request['password']);
            $dm = $request->only(['first_name','middle_name','last_name','password','email','mobile','person_id','added_by','added_on','state_id','remote_id']);
        }
        $dm = DrishteeMitra::create($dm);
        Mail::to($dm->email)->send(new DmCreateMail($dm));
        sendSMS('Welcome to Drishtee Foundation. Your Drishtee Mitra account has been created. Download the app http://bit.ly/MiriMarketBarter.',$dm->mobile);
        $dm->createDmTransaction('Success',0);

        $ledger = Ledger::where('ledger_id',$dm->id)->first();
        $dm->ledger_id = $ledger->id;
        $dm->save(); 
        $dmg['dm_id'] = $dm->id;
        $dmg['added_by'] = $request['added_by'];
        $dmg['added_on']= $data['added_on'];
        $dmg['geography_id'] = $request['geography_id'];
        $dmg['geography_type'] = $request['geography_type'];
        $dmg = DMGeography::create($dmg);
        $ndata = array();
        $dp = $dm->dmProfile()->create($ndata);
        $person->dm_id = $dm->id;
        $person->save();
        return response()->json($dm->load('dmProfile'), 201);
    }
    public function updateDrishteeMitra(Request $request)
    {
        $validation = Validator::make($request->all(),[
             'old_first_name'                 => 'required',
             'old_last_name'                  => 'required',
             'old_middle_name'                => 'sometimes',
             'email'                      => 'required|email',
             'mobile'                     => 'required',
             'geography_name'             => 'required',
             'state_name'                 => 'required'  
        ]);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,400);
        }
        $dm = DrishteeMitra::where('email',$request['email'])->where('mobile',$request['mobile'])->first();
        if($dm ) {
            $geography = Geography::where('name',$request['geography_name'])->first();
        $state = State::where('name',$request['state_name'])->first();
        $data['first_name']= $request['first_name'];
        if(isset($request['middle_name'])) {
        $data['middle_name'] = $request['middle_name'];
        }
        else {
            $data['middle_name'] = '';
        }
        $data['last_name'] = $request['last_name'];
        $data['email'] = $request['email'];
        $data['mobile'] = $request['mobile'];
        $data['geography_id'] = $geography->id;
        $data['geography_type'] = $geography->type;
        // $data['dm_id'] = $request['added_by'];
        $data['added_on'] = $request['added_on'] = Carbon::now()->toDateTimeString();
        $data['state_id'] = $state->id;
        $person = Person::create($data);
        $person->createPersonTransaction('Success',0);
        $person_ledger = Ledger::where('ledger_id',$person->id)->first();
        $person->ledger_id = $person_ledger->id;
        $person->save(); 
        $request['person_id'] = $person->id;
        $user = User::where('is_super_admin',true)->first(); 
        $request['added_by'] = $user->id;
        $dm = $request->only(['first_name','middle_name','last_name','email','mobile']);
        $request['password'] = 123456;
        $request['state_id'] = $state->id;
        $request['geography_id'] = $geography->id;
        $request['geography_type'] = $geography->type;
        if(isset($request['password'])) {
            // $password = '123456';
            $request['password'] = Hash::make($request['password']);
            $dm = $request->only(['first_name','middle_name','last_name','password','email','mobile','person_id','added_by','added_on','state_id']);
        }
        $dm = DrishteeMitra::create($dm);
        Mail::to($dm->email)->send(new DmCreateMail($dm));
        sendSMS('Welcome to Drishtee Foundation. Your Drishtee Mitra account has been created. Download the app http://bit.ly/MiriMarketBarter.',$dm->mobile);
        $dm->createDmTransaction('Success',0);

        $ledger = Ledger::where('ledger_id',$dm->id)->first();
        $dm->ledger_id = $ledger->id;
        $dm->save(); 
        $dmg['dm_id'] = $dm->id;
        $dmg['added_by'] = $request['added_by'];
        $dmg['added_on']= $data['added_on'];
        $dmg['geography_id'] = $request['geography_id'];
        $dmg['geography_type'] = $request['geography_type'];
        $dmg = DMGeography::create($dmg);
        $ndata = array();
        $dp = $dm->dmProfile()->create($ndata);
        $person->dm_id = $dm->id;
        $person->save();
        return response()->json($dm->load('dmProfile'), 201);
        }
        
    }
    public function deleteDm($id){
        $dm = DrishteeMitra::find($id);
        if($dm) {
            $services = $dm->services;
            $addedProduct = $dm->productAdded;
            $product = $dm->personProduct;
            $barters = $dm->addedBarters;
          //  $geography = $dm->dmGeography;
         //   $state = $dm->state;
            $dispute = $dm->dispute;
          //  $personAdd = $dm->personAddedBy;
            if(count($services) > 0 || count($addedProduct) > 0 || count($product) > 0 || count($barters) > 0 || count($dispute) > 0 ) {
                return response()->json(['error'=>'you can not delete this DM'],400);

            }
            else{
                $dm->destroy($id);
                return response()->json(true,200);
            }
            
        }
        return response()->json(['error'=>'DM Not Found'],404);
    }
}
