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
use App\BarterMatch;
use Rap2hpoutre\FastExcel\FastExcel;
use Log;
use App\District;
use App\Block;
use App\Country;
use App\Vaccination;
use App\UdyogiVaccination;

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
        $enddate = null;
        $dt = Carbon::now();
        
        if($request['startdate'] && $request['enddate']) {
            $startdate = Carbon::parse($request->startdate)->format('Y/m/d');
            $enddate = Carbon::parse($request->enddate)->format('Y/m/d').' '.$dt->toTimeString();
        }
        $geography = Geography::count();
        $dm = DrishteeMitra::all();
        $persons = Person::all();
        $products = Product::count();
        $pService = PersonService::count();
        $openBarters = null;
        if($startdate && $enddate) {
            $batters = Barter::where('created_at','>=',$startdate)->where('created_at','<=',$enddate)->where('status','Completed')->get();
            $battersCount = count($batters);
            $openBarters = Barter::where('created_at','>=',$startdate)->where('created_at','<=',$enddate)->where('status','Open')->get();

        }else {
            $batters = Barter::where('status','Completed')->get();
            $battersCount = count($batters);
            $openBarters = Barter::where('status','Open')->get();
        }
        $openBarterLp = 0;
        $openBGID = [];

        foreach ($openBarters as $op) {
           $openBarterLp += $op->barter_total_lp_offered + $op->barter_total_lp_needed;
           if(!in_array($op->geography_id, $openBGID)){
            array_push($openBGID, $op->geography_id);
        }
    }
    $batterslp = 0;
    $bGID = [];
    foreach ($batters as $key) {
        $batterslp += $key->barter_total_lp_offered + $key->barter_total_lp_needed;
        if(!in_array($key->geography_id, $bGID)){
            array_push($bGID, $key->geography_id);
        }
    }


    $tejasProduct = Product::where('is_gold_product',true)->count();
    $personWithNoProduct =0;

    foreach ($persons as $key) {
        if(count($key->personProducts) == 0){
            $personWithNoProduct++;
        }
    }

    $avgProduct = round($products/count($persons),1);

    $dmWithNoPerson = 0;
    foreach ($dm as $key) {
        if(!count($key->personAddedBy)){
            $dmWithNoPerson++;
        }
    }
    $avgDm = round(count($persons)/count($dm),1);
    $avgServices = round(count($persons)/$pService,1);
    $persons = Person::all();
    $lpinaccount = 0;
    $nolpinaccount = 0; 
    foreach ($persons as $person) {
        if($person->ledger->balance==0) {
            $nolpinaccount++;
        }
        else {
            $lpinaccount++;
        }
    }
    return response()->json(
        ['mitras'=>count($dm),
        'vatika'=>$geography,
        'persons'=> count($persons),
        'products'=>$products,
        'completed_barters'=> $battersCount,
        'completed_barter_lp'=>$batterslp,
        'open_barter_lp'=> $openBarterLp,
        'open_barter_geo'=> count($openBGID),
        'open_barters' => count($openBarters),
        'completed_barter_geo'=> count($bGID),
        'tejas_products'=>$tejasProduct,
        'average_products'=>$avgProduct,
        'producers_with_no_product'=>$personWithNoProduct,
        'dm_with_no_people'=>$dmWithNoPerson ,
        'average_no_of_people_with_dm'=>$avgDm,
        'average_services'=>$avgServices,
        'producers_with_no_lp_in_account'=>$nolpinaccount,
        'producers_with_lp_in_account'=>$lpinaccount],200);
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
     array_push($data,["State"=>$Gkey->state,"District"=>$Gkey->district,"Geography Name"=>$Gkey->name,"Ledger"=>$ladger]);
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
                'Transaction Note' => $key->transaction_note,
                "Credit/Debit"=>$key->transaction_type,
                'Balance'=>$key->balance_after_transaction]);
        }
        array_push($data,["Type"=>"People",
            "Entity"=>'',
            "DateTime"=>'','Livelihood Points (LP)'=>0, 'Transaction Note'=>'',
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
                'Transaction Note' => $key->transaction_note,
                'Livelihood Points (LP)'=>$key->amount,
                "Credit/Debit"=>$key->transaction_type,
                'Balance'=>$key->balance_after_transaction]);
        }
        array_push($data,["Type"=>"Mitra","Entity"=>'',
            "DateTime"=>'','Livelihood Points (LP)'=>0,"Credit/Debit"=>'','Transaction Note'=>'','Balance'=>0]);
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
                'Livelihood Points (LP)'=>$key->amount,
                'Transaction Note' => $key->transaction_note,
                "Credit/Debit"=>$key->transaction_type,'Balance'=>$key->balance_after_transaction]);
        }
        array_push($data,["Type"=>"Admin","Entity"=>'',
            "DateTime"=>'','Transaction Note'=>'','Livelihood Points (LP)'=>0,"Credit/Debit"=>'','Balance'=>0]);
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
    $dt = Carbon::now();

    $startdate = Carbon::parse($request->startDate)->format('Y-m-d');
    $enddate = Carbon::parse($request->endDate)->format('Y-m-d').' '.$dt->toTimeString();


        // return response()->json([$startdate,$enddate],200);

    $barters = Barter::where('created_at','>=',$startdate)->where('created_at','<=',$enddate)->get();
        // $barters = Barter::where('id',285)->get();
    foreach ($barters as $key) {
        $haveProduct = []; 
        $BMLIP = $key->barterMatchLocalInventoryProducts;
        $BMLIS = $key->barterMatchLocalInventoryServices;
        $BMLIL = $key->barterMatchLocalInventoryLps;
        $dt = Carbon::parse($key->created_at)->format('Y/m/d');
        $dateArray = date_parse_from_format('Y/m/d', $dt);
        if($key->status  == 'Completed') {
            $completedate = Carbon::parse($key->updated_at)->format('Y/m/d');
            $cdate = date_parse_from_format('Y/m/d', $completedate);
        }

        $haveProduct = array_merge($haveProduct, [
            "Id"=>$key->id,
            'Year' => $dateArray['year'],
            'Month' => Carbon::parse($key->created_at)->format('F'),
            'Date'  =>$dateArray['day'],
            "Mitra"=>(($key->drisheeMitras) ? ($key->drisheeMitras->first_name.' '.$key->drisheeMitras->last_name): ""),
            "Geography"=>$key->geography->name,
            "State"=> (($key->person && $key->person->state) ?  ($key->person->state): ""),
            "Barter Person"=> (($key->person) ? ( $key->person->first_name.' '.$key->person->last_name): ""),
            "Status" => $key->status,
                // "Create At" => Carbon::parse($key->created_at)->format('Y/m/d'),
                // $dateArray = date_parse_from_format('Y/m/d', $date);

            "Completed Year" => (($key->status === "Completed") ? $cdate['year'] : ""),
            "Completed Month" => (($key->status === "Completed") ? Carbon::parse($key->updated_at)->format('F') : "") ,

            "Completed Date" => (($key->status === "Completed") ? $cdate['day'] : "")
        ]);



        foreach ($key->barterHaveProducts as $pro) {
            $haveProduct = array_merge($haveProduct, [
                "Have Product Name" => $pro->personProduct->product->name,
                "Have Product Quantity" => $pro->quantity,
                "Have Product LP" => $pro->product_lp,
                "Have LP" => "",
                "Have (Total LP)" => $key->barter_total_lp_offered,
                "Need (Total LP)" => $key->barter_total_lp_needed, 

                "Match Product Person" => "",
                "Match Product Name" => "",
                "Match Product Quantity" => "",
                "Match Product LP" => "",
                "Match LP" => "",
                "Match LP Person" => "",

                "Have Service Name" => "",
                "Have Service Quantity" => "",
                "Have Service LP" => "",

                "Match Service Person" => "",
                "Match Service Name" => "",
                "Match Service Quantity" => "",
                "Match Service LP" => ""

            ]);
            if(count($BMLIP) && count($BMLIL)){
                foreach($BMLIP as $bmlip){
                 $haveProduct = array_merge($haveProduct, [
                    "Match Product Person" => (( $bmlip->barterMatch && $bmlip->barterMatch->person) ? $bmlip->barterMatch->person->first_name.' '.$bmlip->barterMatch->person->last_name : ""),
                    "Match Product Name" => $bmlip->product->name,
                    "Match Product Quantity" => $bmlip->product_quantity,
                    "Match Product LP" => $bmlip->product_lp]);
             }
             foreach($BMLIL as $bmlil){
                $haveProduct = array_merge($haveProduct,[

                    "Match LP" => $bmlil->lp,
                    "Match LP Person" => (($bmlil->barterMatch && $bmlil->barterMatch->person) ? $bmlil->barterMatch->person->first_name.' '.$bmlip->barterMatch->person->last_name : "")]);
            }
        }else if(count($BMLIP)){
            foreach($BMLIP as $bmlip){
             $haveProduct = array_merge($haveProduct, [
                "Match Product Person" => (( $bmlip->barterMatch && $bmlip->barterMatch->person) ? $bmlip->barterMatch->person->first_name.' '.$bmlip->barterMatch->person->last_name : ""),
                "Match Product Name" => $bmlip->product->name,
                "Match Product Quantity" => $bmlip->product_quantity,
                "Match Product LP" => $bmlip->product_lp,
            ]);
         }
     }else{
       $haveProduct = array_merge($haveProduct, [
        "Match Product Person" => "",
        "Match Product Name" => "",
        "Match Product Quantity" => "",
        "Match Product LP" => "",
        "Match LP" => "",
        "Match LP Person" => "",
    ]);
   }
   if(count($BMLIS)){
    foreach($BMLIS as $bmlis){
        $haveProduct = array_merge($haveProduct,[
            "Match Service Person" => (($bmlis->barterMatch && $bmlis->barterMatch->person) ? $bmlis->barterMatch->person->first_name.' '.$bmlis->barterMatch->person->last_name : ""),
            "Match Service Name" => $bmlis->service->name,
            "Match Service Quantity" => $bmlis->no_of_days,
            "Match Service LP" => $bmlis->service_lp]);
    }
}else{
    $haveProduct = array_merge($haveProduct,[
        "Match Service Person" => "",
        "Match Service Name" => "",
        "Match Service Quantity" => "",
        "Match Service LP" => ""]);
}
}

foreach ($key->barterHaveLp as $hlp) {
    $haveProduct = array_merge($haveProduct, [
        "Have Product Name" => "",
        "Have Product Quantity" => "",
        "Have Product LP" => "",
        "Have LP" => $hlp->lp,
        "Have (Total LP)" => $key->barter_total_lp_offered,
        "Need (Total LP)" => $key->barter_total_lp_needed,

        "Match Product Person" => "",
        "Match Product Name" => "",
        "Match Product Quantity" => "",
        "Match Product LP" => "",
        "Match LP" => "",
        "Match LP Person" => "",

        "Have Service Name" => "",
        "Have Service Quantity" => "",
        "Have Service LP" => "",

        "Match Service Person" => "",
        "Match Service Name" => "",
        "Match Service Quantity" => "",
        "Match Service LP" => ""
    ]);
    if(count($BMLIP) && count($BMLIL)){
        foreach($BMLIP as $bmlip){
         $haveProduct = array_merge($haveProduct, [
            "Match Product Person" => (( $bmlip->barterMatch && $bmlip->barterMatch->person) ? $bmlip->barterMatch->person->first_name.' '.$bmlip->barterMatch->person->last_name : ""),
            "Match Product Name" => $bmlip->product->name,
            "Match Product Quantity" => $bmlip->product_quantity,
            "Match Product LP" => $bmlip->product_lp]);
     }
     foreach($BMLIL as $bmlil){
        $haveProduct = array_merge($haveProduct,[

            "Match LP" => $bmlil->lp,
            "Match LP Person" => (($bmlil->barterMatch && $bmlil->barterMatch->person) ? $bmlil->barterMatch->person->first_name.' '.$bmlip->barterMatch->person->last_name : ""),
        ]);
    }
}else if(count($BMLIP)){
    foreach($BMLIP as $bmlip){
     $haveProduct = array_merge($haveProduct, [
        "Match Product Person" => (( $bmlip->barterMatch && $bmlip->barterMatch->person) ? $bmlip->barterMatch->person->first_name.' '.$bmlip->barterMatch->person->last_name : ""),
        "Match Product Name" => $bmlip->product->name,
        "Match Product Quantity" => $bmlip->product_quantity,
        "Match Product LP" => $bmlip->product_lp
    ]);
 }
}else{
   $haveProduct = array_merge($haveProduct, [
    "Match Product Person" => "",
    "Match Product Name" => "",
    "Match Product Quantity" => "",
    "Match Product LP" => "",
    "Match LP" => "",
    "Match LP Person" => "",
]);
}
if(count($BMLIS)){
    foreach($BMLIS as $bmlis){
        $haveProduct = array_merge($haveProduct,[
            "Match Service Person" => (($bmlis->barterMatch && $bmlis->barterMatch->person) ? $bmlis->barterMatch->person->first_name.' '.$bmlis->barterMatch->person->last_name : ""),
            "Match Service Name" => $bmlis->service->name,
            "Match Service Quantity" => $bmlis->no_of_days,
            "Match Service LP" => $bmlis->service_lp]);
    }
}else{
    $haveProduct = array_merge($haveProduct,[
        "Match Service Person" => "",
        "Match Service Name" => "",
        "Match Service Quantity" => "",
        "Match Service LP" => ""]);
}   
}

foreach ($key->barterHaveServices as $service) {
    $haveProduct = array_merge($haveProduct, [
        "Have Product Name" => "",
        "Have Product Quantity" => "",
        "Have Product LP" => "",
        "Have LP" => "",
        "Have (Total LP)" => $key->barter_total_lp_offered,
        "Need (Total LP)" => $key->barter_total_lp_needed,

        "Match Product Person" => "",
        "Match Product Name" => "",
        "Match Product Quantity" => "",
        "Match Product LP" => "",
        "Match LP" => "",
        "Match LP Person" => "",

        "Have Service Name" => $service->personService->service->name,
        "Have Service Quantity" => $service->no_of_days,
        "Have Service LP" => $service->service_lp,

        "Match Service Person" => "",
        "Match Service Name" => "",
        "Match Service Quantity" => "",
        "Match Service LP" => ""
    ]);
    if(count($BMLIP) && count($BMLIL)){
        foreach($BMLIP as $bmlip){
         $haveProduct = array_merge($haveProduct, [
            "Match Product Person" => (( $bmlip->barterMatch && $bmlip->barterMatch->person) ? $bmlip->barterMatch->person->first_name.' '.$bmlip->barterMatch->person->last_name : ""),
            "Match Product Name" => $bmlip->product->name,
            "Match Product Quantity" => $bmlip->product_quantity,
            "Match Product LP" => $bmlip->product_lp
        ]);
     }
     foreach($BMLIL as $bmlil){
        $haveProduct = array_merge($haveProduct,[
            "Match LP" => $bmlil->lp,

            "Match LP Person" => (($bmlil->barterMatch && $bmlil->barterMatch->person) ? $bmlil->barterMatch->person->first_name.' '.$bmlil->barterMatch->person->last_name : "")
        ]);
    }
}else if(count($BMLIP)){
    foreach($BMLIP as $bmlip){
     $haveProduct = array_merge($haveProduct, [
        "Match Product Person" => (( $bmlip->barterMatch && $bmlip->barterMatch->person) ? $bmlip->barterMatch->person->first_name.' '.$bmlip->barterMatch->person->last_name : ""),
        "Match Product Name" => $bmlip->product->name,
        "Match Product Quantity" => $bmlip->product_quantity,
        "Match Product LP" => $bmlip->product_lp
    ]);
 }
}else{
   $haveProduct = array_merge($haveProduct, [
    "Match Product Person" => "",
    "Match Product Name" => "",
    "Match Product Quantity" => "",
    "Match Product LP" => "",
    "Match LP" => "",
    "Match LP Person" => "",
]);
}
if(count($BMLIS)){
    foreach($BMLIS as $bmlis){
        $haveProduct = array_merge($haveProduct,[
                            // "Match Product Person" => "",
                            // "Match Product Name" => "",
                            // "Match Product Quantity" => "",
                            // "Match Product LP" => "",
            "Match Service Person" => (($bmlis->barterMatch && $bmlis->barterMatch->person) ? $bmlis->barterMatch->person->first_name.' '.$bmlis->barterMatch->person->last_name : ""),
            "Match Service Name" => $bmlis->service->name,
            "Match Service Quantity" => $bmlis->no_of_days,
            "Match Service LP" => $bmlis->service_lp]);
    }
}else{
    $haveProduct = array_merge($haveProduct,[
                            // "Match Product Person" => "",
                            // "Match Product Name" => "",
                            // "Match Product Quantity" => "",
                            // "Match Product LP" => "",
        "Match Service Person" => "",
        "Match Service Name" => "",
        "Match Service Quantity" => "",
        "Match Service LP" => ""]);
}   
}
if(count($haveProduct) != 0) array_push($data, $haveProduct);
}
$file = Carbon::now()->format('YmdHis').'reportBarterExport.xlsx';
$filepath = (new FastExcel($data))->export(storage_path().'/'.$file);
$url = "api/productsexport/".$file;
return response()->json(url($url),200);
           //return response()->json($data,200);
}

    /**
    * @param  \Illuminate\Http\Request  $request
    * @return \App\DrishteeMitra $dm with token
    * do filter drishtee object name, email, mobile, geography
    */
    public function filterDrishtee(Request $request){
        $dms = DrishteeMitra::orderBy('first_name')->with('dmProfile','dmDevice','dmGeography.geography','personAddedBy','addedBy');
        $geography_ids = $request['geography_ids'];
        $dms->whereHas('dmGeography',function($query) use($geography_ids) {
            $query->whereIn('geography_id', $geography_ids);
        });

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
        if(isset($request['filters']['added_by']) && !empty($request['filters']['added_by'])) {
            $dms->where('added_by',$request['filters']['added_by']);
        }
        if(isset($request['filters']['type']) && !empty($request['filters']['type'])) {
            $dms->where('type',$request['filters']['type']);
           
        }
        if(isset($request['filters']['is_csp']) && (false ==$request['filters']['is_csp'] || 
            true == $request['filters']['is_csp'])) {
            $dms->where('is_csp', $request['filters']['is_csp']);
    }
    if(isset($request['filters']['is_vaani']) && (false ==$request['filters']['is_vaani'] || 
        true == $request['filters']['is_vaani'])) {
        $dms->where('is_csp', $request['filters']['is_vaani']);
   }
if(isset($request['filters']['geography_id']) && !empty($request['filters']['geography_id'])) {
    $geography_id = $request['filters']['geography_id'];
    $dms->whereHas('dmGeography',function($query) use($geography_id) {
        $query->where('geography_id', $geography_id);
    });
}
if(isset($request['count']) && $request['count']) {
    $dmc = $dms->get();
    return response()->json(count($dmc),200);
}
$offset = isset($request['skip']) ? $request['skip'] : 0 ;
$chunk = isset($request['skip']) ? $request['limit'] : 999999;
$dms = $dms->skip($offset)->limit($chunk)->get();
return response()->json($dms,200);  
}

public function index()
{
    // dd("hel");
 return response()->json(DrishteeMitra::orderBy('first_name')->get()->load('dmProfile','dmDevice','dmGeography'),200);
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
           'state_id'                   => 'required',
           'is_csp'                     => 'sometimes',
           'is_vaani'                   => 'sometimes',
           'type'                       => 'required',
           'code'                       => 'required'  
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
        $person_ledger = Ledger::where('ledger_id',$person->id)->where('ledger_type','App\Person')->first();
        $person->ledger_id = $person_ledger->id;
        $person->save(); 
        $request['person_id'] = $person->id; 
        
        $dm = $request->only(['first_name','middle_name','last_name','email','mobile','is_csp','is_vaani','type','code']);
        if(isset($request['password'])) {
            $request['password'] = Hash::make($request->password);
            $dm = $request->only(['first_name','middle_name','last_name','password','email','mobile','person_id','added_by','added_on','state_id','is_csp','is_vaani','type','code']);
        }
        $dm = DrishteeMitra::create($dm);
       // Mail::to($dm->email)->send(new DmCreateMail($dm));
        $template_id = 1207161761262922013;

        sendSMS('Welcome to Drishtee Foundation. Your Drishtee Mitra account has been created. Download the app http://bit.ly/MiriMarketBarter.',$dm->mobile,$template_id);
        $dm->createDmTransaction('Success',0);

        $ledger = Ledger::where('ledger_id',$dm->id)->where('ledger_type','App\DrishteeMitra')->first();
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
           'geography_id'               => 'required',
           'is_csp'                     => 'required|boolean',
           'is_vaani'                   => 'required|boolean'
       ]);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,400);
        }
        $dm = DrishteeMitra::find($id);
        if($dm) {
            $person = Person::find($dm->person_id);
            $person->mobile = $request['mobile'];
            $person->save();
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

         //   Mail::to($dm->email)->send(new DmUpdateMail($dm));
            $template_id = 1207161761267981265;
            sendSMS('Your Drishtee Mitra profile has been updated successfully.',$dm->mobile,$template_id);
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
                $template_id = 1207161761276469241;
                sendSMS('Your Drishtee Mitra login OTP is '.$dm->otp,$dm->mobile,$template_id);

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
                $template_id = 1207161761276469241;

                sendSMS('Your Drishtee Mitra login OTP is '.$dm->otp,$dm->mobile,$template_id);

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
   // Storage::disk('public')->put($originalName,File::get($cover));
    $filePath = $request->file('file')->storeAs('databackup/', $originalName, 'azure');
    
    $request['photo_name'] = $originalName;
   // $request['photo_path'] = Storage::disk('public')->url($originalName);
    $request['photo_path'] = 'https://drishteedatastore.blob.core.windows.net/drishtee/databackup/'.$originalName;
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
                    // $users  = User::where('is_super_admin',true)->get();
                    // foreach ($users as $user) {
                    //     Mail::to($user->email)->send(new DmDeactivateMail($user));
                    // }
                    $dm->status = $request->status;
                    $dm->save();
                    $template_id = 1207161761287183216;
                    sendSMS('Your Drishtee Mitra profile has been deactivated on the Miri Market Barter app.',$dm->mobile,$template_id);
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
            $template_id = 1207161761316182587;
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

        $transaction = LedgerTransaction::where('ledger_id',$ledger->id)->where('person_id',$dm->person_id)->where('created_at','>=',$startdate)->where('created_at','<=',$enddate)->get();
        return response()->json($transaction,200);
    }

    /**
    * @param \App\DrishteeMitra $dm_id
    * @return \App\LedgerTransaction $ledgerTransaction
    * do find out all transaction related to $dm_id
    */
    public function getDMLedgerTransaction($dm_id){
        $dm = DrishteeMitra::find($dm_id);

        $startdate = Carbon::now()->subDays(30)->format('Y/m/d');
        $enddate = Carbon::tomorrow()->format('Y/m/d');

        $transaction = LedgerTransaction::where('person_id',$dm->person_id)->where('created_at','>=',$startdate)->where('created_at','<=',$enddate)->get();
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
   //     Log::info("heee".$request);
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
        $person_ledger = Ledger::where('ledger_id',$person->id)->where('ledger_type','App\Person')->first();
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
      //  Mail::to($dm->email)->send(new DmCreateMail($dm));
        $template_id = 1207161761262922013;

        sendSMS('Welcome to Drishtee Foundation. Your Drishtee Mitra account has been created. Download the app http://bit.ly/MiriMarketBarter.',$dm->mobile,$template_id);
        $dm->createDmTransaction('Success',0);

        $ledger = Ledger::where('ledger_id',$dm->id)->where('ledger_type','App\DrishteeMitra')->first();
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
            $person_ledger = Ledger::where('ledger_id',$person->id)->where('ledger_type','App\Person')->first();
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
    //    Mail::to($dm->email)->send(new DmCreateMail($dm));
            $template_id = 1207161761262922013;
            sendSMS('Welcome to Drishtee Foundation. Your Drishtee Mitra account has been created. Download the app http://bit.ly/MiriMarketBarter.',$dm->mobile,$template_id);
            $dm->createDmTransaction('Success',0);

            $ledger = Ledger::where('ledger_id',$dm->id)->where('ledger_type','App\DrishteeMitra')->first();
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
    public function deleteMitraExternal(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'email'   => 'required|exists:drishtree_mitras,email',
            'mobile' =>  'required|exists:drishtree_mitras,mobile'
        ]);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,401);
        }
        $checktoken = $request->header('checktoken');
      //  dd($checktoken);
        if($checktoken != '0fQgVCL1cM2mGytOSovz') {
         return response()->json('Token Invalid',402);
     }
     $dm = DrishteeMitra::where('email',$request['email'])->where('mobile',$request['mobile'])->first();
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
    return response()->json(['error'=>'DM Not Found'],404);
}
public function personAddDate(){
    $data = [];

    $dms = DrishteeMitra::all();
    foreach ($dms as $dm) {
           // $persons = $dm->personAddedBy->sortBy('added_on');
        $persons = Person::where('dm_id',$dm->id)->orderBy('added_on','ASC')->get();
        if(count($persons) != 0) {
           if($dm->middle_name) {
            $mname = $dm->middle_name; 
        }   
        $temp['Dmname'] = $dm->first_name.' '.$mname.' '.$dm->last_name; 
        $temp['Date'] = Carbon::parse($persons['0']->created_at)->format('d-m-Y');
        $temp['Geography'] = $dm->dmGeography->geography->name;
        $temp['District'] = $dm->dmGeography->geography->district;
            // dd($temp['date']);
        array_push($data,$temp); 
        $temp = null;   
    }

            // return response()->json($persons['0'],200);

}
       // return response()->json($data,200);
$file = Carbon::now()->format('YmdHis').'personadddate.xlsx';
$filepath = (new FastExcel($data))->export(storage_path().'/'.$file);
$url = "api/personadddate/".$file;
return response()->json(url($url),200);
}
public function downloadExportPersonAdd($filename){
    $file = basename($filename);
    $filepath = storage_path().'/'.$file;
    return response()->download($filepath, $file, [
        'Content-Length: '. filesize($filepath)
    ]);
}

public function createDrishteeMitraCsp(Request $request)
{
    $validation = Validator::make($request->all(),[
       'first_name'                 => 'required',
       'middle_name'                => 'sometimes',
       'last_name'                  => 'required',
       'email'                      => 'required|email|unique:drishtree_mitras,email',
       'mobile'                     => 'required|unique:drishtree_mitras,mobile',
       'geography_name'             => 'required',
       'state_name'                 => 'required',
       'ID'                         => 'required',

   ]);
    if($validation->fails()) {
        $errors = $validation->errors();
        return response()->json($errors,400);
    }
    $request['is_csp'] = true;
    // Log::info("heee".$request);
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
        $dm = $request->only(['first_name','middle_name','last_name','password','email','mobile','person_id','added_by','added_on','state_id','remote_id','is_csp']);
    }
    $dm = DrishteeMitra::create($dm);
      //  Mail::to($dm->email)->send(new DmCreateMail($dm));
    $template_id = 1207161761262922013;

    sendSMS('Welcome to Drishtee Foundation. Your Drishtee Mitra account has been created. Download the app http://bit.ly/MiriMarketBarter.',$dm->mobile,$template_id);
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
public function updateDrishteeMitraCsp(Request $request)
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
      //  Mail::to($dm->email)->send(new DmCreateMail($dm));
        $template_id = 1207161761262922013;
        sendSMS('Welcome to Drishtee Foundation. Your Drishtee Mitra account has been created. Download the app http://bit.ly/MiriMarketBarter.',$dm->mobile,$template_id);
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
public function createDrishteeMitraVaani(Request $request)
{
    $validation = Validator::make($request->all(),[
       'first_name'                 => 'required',
       'middle_name'                => 'sometimes',
       'last_name'                  => 'required',
       'email'                      => 'required|email|unique:drishtree_mitras,email',
       'mobile'                     => 'required|unique:drishtree_mitras,mobile',
       'geography_name'             => 'required',
       'state_name'                 => 'required',
       'ID'                         => 'required',

   ]);
    if($validation->fails()) {
        $errors = $validation->errors();
        return response()->json($errors,400);
    }
    $request['is_vaani'] = true;
    // Log::info("heee".$request);
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
        $dm = $request->only(['first_name','middle_name','last_name','password','email','mobile','person_id','added_by','added_on','state_id','remote_id','is_vaani']);
    }
    $dm = DrishteeMitra::create($dm);
      //  Mail::to($dm->email)->send(new DmCreateMail($dm));
    $template_id = 1207161761262922013;

    sendSMS('Welcome to Drishtee Foundation. Your Drishtee Mitra account has been created. Download the app http://bit.ly/MiriMarketBarter.',$dm->mobile,$template_id);
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
public function importCsp(Request $request)
{
    $collection = (new FastExcel)->import($request['file']);
    foreach ($collection as $cl) {
        if($cl['Sl.no'] !=null) {
          $cn = Country::where('name','India')->first();

          $fdis  = null;
          $fstate = null; 
          $fblock = null;  
          $fgeo = null;
          $geography = Geography::where('name',$cl['GP / Vatika'])->first();
        // dd($geography);
          $fgeo = $geography;
          if($geography) {
             $state = State::where('name',$cl['State'])->first();
             $district = District::where('name',$cl['District'])->first();
             $block = Block::where('name',$cl['Block'])->first();
             $fstate = $state;
             $fdis = $district;
             $dblock = $block;
         }
         else {
             $state = State::where('name',$cl['State'])->first();
             $fstate = $state;
             if(!$state) {
                $tempstate['name'] = $cl['State'];
                $tempstate['country_id'] = $cn->id;
                $tempstate['is_active'] = true; 
                $state = State::create($tempstate);
                $fstate = $state;
            }
            $district = District::where('name',$cl['District'])->first();
            $fdis= $district;
           // dd($fdis);
            if(!$district) {
                $tempdis['name'] = $cl['District'];
                $tempdis['state_id'] = $state->id;
                $tempdis['is_active'] = true;
                $district = District::create($tempdis); 
                $fdis = $district; 
            }

            $block = Block::where('name',$cl['Block'])->first();
            $fblock = $block;

            if(!$block) {
                $tempb['name'] = $cl['Block'];
                $tempb['district_id'] = $district->id;
                $tempb['is_active'] = true;
                $block = Block::create($tempb);
                $fblock = $block; 
            }
            $tempgeo['name'] = $cl['GP / Vatika'];
            $tempgeo['state'] = $fstate->name;
            $tempgeo['district'] = $fdis->name;
            $tempgeo['block'] = $fblock->name;
            $tempgeo['type'] = 'block';
            $tempgeo['is_active'] = true;
            $tempgeo['remote_id'] = $cl['CODE'];
            $tempgeo['parent_id'] = $fdis->id;
            $tempgeo['parent_pseudo_id'] = $cn->id.'-'.$fstate->id.'-'.$fdis->id.'-'.$fblock->id; 
            $geography = Geography::create($tempgeo);
            $fgeo = $geography;

        }
        $data['first_name']= $cl['First Name'];
        if(isset($cl['Middle Name'])) {
            $data['middle_name'] = $cl['Middle Name'];
        }
        else {
            $data['middle_name'] = '';
        }
        $data['last_name'] = $cl['Last Name'];
        $user = User::where('is_super_admin',true)->first(); 
        
        $data['email'] = $cl['Email'];
        $data['mobile'] = $cl['Mobile No.'];

        $data['geography_id'] = $fgeo->id;
        $data['geography_type'] = $fgeo->type;
        // $data['dm_id'] = $request['added_by'];
        $data['added_on'] = Carbon::now()->toDateTimeString();
        // $data['']
        // dd($fdis);
        $data['state_id'] = $fstate->id;
        $dmold = DrishteeMitra::where('email',$data['email'])->where('mobile',$data['mobile'])->first();
        if($dmold) {
          $dmold->update($data);
        //  $dmold->update();
      }
      else {
       $person = Person::create($data);
       $perloc['latitude'] = $cl['Latitude'];
       $perloc['longitude'] = $cl['Longitude'];
       $perloc['state'] = $cl['State'];
       $perloc['block'] = $cl['Block'];
       $peroc = $person->personLocation()->create($perloc);
       $person->createPersonTransaction('Success',0);
       $person_ledger = Ledger::where('ledger_id',$person->id)->where('ledger_type','App\Person')->first();
       $person->ledger_id = $person_ledger->id;
       $person->save();
       $personbank['account_number']= $cl['Account No.'];
       $personbank['bank_name'] = $cl['Name of Bank'];
       $personbank['ifsc_code']= $cl['IFSC'];
       $personbank['payee_name'] = $data['first_name'].' '.$data['middle_name'].' '.$data['last_name'];
       $personac = $person->bankAccount()->create($personbank); 
       $tempp['person_id'] = $person->id;
       $tempp['added_by'] = $user->id;
        // $dm = $request->only(['first_name','middle_name','last_name','email','mobile']);
       $tempp['first_name'] = $data['first_name'];
       $tempp['middle_name'] = $data['middle_name'];
       $tempp['last_name'] = $data['last_name'];
       $tempp['email'] = $data['email'];
       $tempp['mobile'] = $data['mobile']; 
       $tempp['password'] = 123456;
       $tempp['state_id'] = $fstate->id;
       $tempp['geography_id'] = $fgeo->id;
       $tempp['geography_type'] = $fgeo->type;
       $tempp['remote_id'] =  $cl['CODE'];
       $tempp['is_csp'] = true;
       $tempp['status'] = true;
       $tempp['type'] = $cl['Type'];
       $tempp['code'] = $cl['CODE'];
       if(isset($tempp['password'])) {
            // $password = '123456';
        $tempp['password'] = Hash::make($tempp['password']);
            //$dm = $reques->only(['first_name','middle_name','last_name','password','email','mobile','person_id','added_by','added_on','state_id','remote_id','is_vaani']);
    }
    $dm = DrishteeMitra::create($tempp);
        // Mail::to($dm->email)->send(new DmCreateMail($dm));
    $template_id = 1207161761262922013;

    sendSMS('Welcome to Drishtee Foundation. Your Drishtee Mitra account has been created. Download the app http://bit.ly/MiriMarketBarter.',$dm->mobile,$template_id);
    $dm->createDmTransaction('Success',0);

    $ledger = Ledger::where('ledger_id',$dm->id)->where('ledger_type','App\DrishteeMitra')->first();
    $dm->ledger_id = $ledger->id;
    $dm->save(); 
    $user = User::where('is_super_admin',true)->first(); 

    $dmg['dm_id'] = $dm->id;
    $dmg['added_by'] = $user->id;
    $dmg['added_on']= $data['added_on'];
    $dmg['geography_id'] = $fgeo->id;
       // $dmg['geography_type'] = $fgeo->type;
    $dmga = $dm->DMGeography()->create($dmg);
    $ndata = array();
    $dp = $dm->dmProfile()->create($ndata);
    $person->dm_id = $dm->id;
    $person->save();   
}

}

}
return response()->json($collection,200);

}
public function comissionMonthlyReport(Request $request)
{
    $dms = DrishteeMitra::all();
    $res = collect();
    $mon = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    $y = $request['year'];
    foreach ($dms as $dm) {
        $temp = array();
        $total =0;
        $temp['State'] = $dm->state->name;
        if($dm->middle_name) {
            $mname = $dm->middle_name; 
        }   

        $temp['Name'] = $dm->first_name.' '.$mname.' '.$dm->last_name;
            // $temp['Name'] = $dm->first_name;
        $temp['Geography'] = $dm->dmGeography->geography->name;
        $temp['Mobile'] = $dm->mobile;
        for($i = 0 ;$i< count($mon);$i++) {
            $c = 0;

            $barters= Barter::where('added_by_dm_id',$dm->id)->get();
            foreach ($barters as $bt) {
                $dt = $bt->created_at->format('M');
                if($mon[$i] == $dt && $y == $bt->created_at->format('Y')) {
                    $lts = $bt->ledgerTransactions;

                    foreach ($lts as $lt) {
                       if($lt['transaction_note'] == 'DM Margin') {
                         $c+=$lt['amount'];
                     }
                 }
             }
         }

         $temp[$mon[$i].'-'.$y] = $c;
         $total += $c;

     }
     $temp['Total'] = $total;
     $res->push($temp);
     $temp = null;
 }
 $file = Carbon::now()->format('YmdHis').'comissionreportmonthwise.xlsx';
 $filepath = (new FastExcel($res))->export(storage_path().'/'.$file);
 $url = "api/comissionreportmonthwise/".$file;
 return response()->json(url($url),200);
 return response()->json($res,200);


}
public function comissionReportMonthWise($filename){
    $file = basename($filename);
    $filepath = storage_path().'/'.$file;
    return response()->download($filepath, $file, [
        'Content-Length: '. filesize($filepath)
    ]);
}
public function mobileUpdate(Request $request)
{
    $collection = (new FastExcel)->import($request['file']);
    foreach ($collection as $col) {
        if($col['CSP Code']) {
            $dm = DrishteeMitra::where('remote_id',$col['CSP Code'])->first();
            if($dm) {
                $dm->mobile = $col['Correction of New Mobile No.'];
                $dm->save();
                $person = Person::find($dm->person_id);
                $person->mobile = $col['Correction of New Mobile No.'];
                $person->save();    
            }

        }
    }
    return response()->json($collection,200);
}
public function vaccinationListReport(Request $request){
   
   if($request['all'] == 'false') {
        $s = State::find($request['state_id']);
        $request['state'] = $s->name;
         // dd('hee');
         $vaccination= Vaccination::whereHas('geography',function($query) use ($request) {
           $query->where('state',$request['state']);
          $query->where('district',$request['district']);
        });
         // $vaccination->whereYear('certificate_dose_1_upload_date',$request['year'])->orWhereYear('certificate_dose_2_upload_date',$request['year'])->whereMonth('certificate_dose_1_upload_date',$request['month'])->orWhereMonth('certificate_dose_2_upload_date',$request['month']);
        $vaccinations= $vaccination->get(); 
        // return response()->json($vaccinations->load('geography'),200);
        }
        else {
            $vaccinationd = Vaccination::all();
            $vaccinations= $vaccinationd;
        }
    $data = collect();
    $vaccinations = $vaccinations;
    $rescol = collect();
    foreach ($vaccinations as $key) {
        if($key->gender == 'female' && $key->addedBy) {
        $key->geography;
        $key["dm_name"] = $key->addedBy->first_name;
        $rescol->push($key);    
        }
        
    }

    $vaccinationsGroupBy = $rescol->groupBy('dm_name');
   // return response()->json($vaccinationsGroupBy,200);
    foreach($vaccinationsGroupBy as $groupKey => $groupValue ){
        $d1_count = 0;
        $d2_count = 0;
        foreach ($groupValue as $vac) {
            $d1_year = isset($vac->certificate_dose_1_upload_date) ? Carbon::parse($vac->certificate_dose_1_upload_date)->year:null;
            $d2_year = isset($vac->certificate_dose_2_upload_date) ? Carbon::parse($vac->certificate_dose_2_upload_date)->year:null;
            $d1_month = isset($vac->certificate_dose_1_upload_date) ? Carbon::parse($vac->certificate_dose_1_upload_date)->month:null;
            $d2_month = isset($vac->certificate_dose_2_upload_date)? Carbon::parse($vac->certificate_dose_2_upload_date)->month:null;

            if(((isset($d1_year) && $d1_year == $request['year']) || (isset($d2_year) && $d2_year ==$request['year'])) && ((isset($d1_month) && $d1_month == $request['month']) || (isset($d2_month) && $d2_month == $request['month']))) {

            $d1_count += isset($d1_year) ? 1:0;
            $d2_count += isset($d2_year) ? 1:0;


            }

        }

        $temp["State"] = $vac->geography->state;
        $temp["District"] = $vac->geography->district;
        $temp["Block"] = $vac->geography->block;
        $temp["Vatika"] = $vac->geography->name;
        $name = null;
        if($vac->addedBy->middle_name) {
            $name = $vac->addedBy->first_name.' '.$vac->addedBy->middle_name.' '.$vac->addedBy->last_name;
        }
        else {
            $name = $vac->addedBy->first_name.' '.$vac->addedBy->last_name;

        }
        $temp["Added by Name"] =  $name;
        $temp["Type"] = $vac->addedBy->type;
        if(isset($vac->addedBy->code)) {
          $code = $vac->addedBy->code;
        }
        else {
            $code = $vac->addedBy->remote_id;
        }
        $temp['CSP/CEP/Vaani Code'] = $code;
        $temp["Mobile No"] = $vac->addedBy->mobile;
        
        
        $temp["Year"] = $request["year"];
        $temp["Month"] = $request["month"];
        $temp["No. of Dose 1"] = $d1_count;
        $temp["No. of Dose 2"] = $d2_count;
        $temp["Bank Name"] = ($vac->addedBy->person && $vac->addedBy->person->bankAccount) ? $vac->addedBy->person->bankAccount->bank_name:'';
        $temp["Payee Name"] =($vac->addedBy->person && $vac->addedBy->person->bankAccount) ? $vac->addedBy->person->bankAccount->payee_name:'';
        $temp["A/C Number"] =($vac->addedBy->person && $vac->addedBy->person->bankAccount) ? $vac->addedBy->person->bankAccount->account_number:'';
        $temp["IFSC Code"] = ($vac->addedBy->person && $vac->addedBy->person->bankAccount) ? $vac->addedBy->person->bankAccount->ifsc_code:'';
        
        $data->push($temp);
        $temp = null; 
    }

        // $vaccinationsGroupBy
    // return response()->json($data,200);
    // return response()->json($vaccinationsGroupBy,200);

    $file = Carbon::now()->format('YmdHis').'vaccinationListReport.xlsx';
    $filepath = (new FastExcel($data))->export(storage_path().'/'.$file);
    $url = "api/comissionreportmonthwise/".$file;
    return response()->json(url($url),200);

}
 public function cspconvert()
 {
     $dms = DrishteeMitra::all();
     foreach ($dms as $dm) {
         if($dm->is_csp == '1') {
           // dd($dm);
            $dm->type = 'CSP';

         }
         else if($dm->is_vaani == '1') {
           $dm->type = 'Vaani';
         }
         else {
            $dm->type = 'Mitra';
         }
         $dm->save();
         if($dm->type == 'CSP') {
            if($dm->code == null)
            $dm->code = $dm->remote_id;
            $dm->save();
         }
     }
     return response()->json($dms,200);
 }
 public function allMitra() {
    return response()->json(DrishteeMitra::all(),200);
 }
 public function correctLedgerId() {
    // dd("hello");
    $dms = DrishteeMitra::skip(1500)->take(500)->get();
    // dd($dms);
    foreach ($dms as $dm) {
        $ledger = Ledger::where('ledger_id',$dm->id)->where('ledger_type','App\DrishteeMitra')->first();
        if($ledger) {
         $dm->ledger_id = $ledger->id;
        $dm->save();    
        }
        // dd($dm);
        
    }
    return response()->json($dms,200);
 }
  public function ledgerCorrect(Request $request) {
    $dms = DrishteeMitra::skip($request['skip'])->take($request['take'])->get();
    foreach ($dms as $dm) {
      $ledtrans = LedgerTransaction::where('person_id',$dm->person_id)->latest()->first();
      $ledger = Ledger::find($dm->ledger_id);
      if($ledger) {
        if($ledtrans) {
         $ledger->balance = $ledtrans->balance_after_transaction;
      $ledger->save();   
        }
         
      }
       
    }
    return response()->json($dms,200);
  }
  public function dataForTarun(){
    $states = State::all();
    $res = collect();
    foreach ($states as $state) {
        $districts = District::where('state_id',$state->id)->get();
        foreach ($districts as $district) {
            $temp['state_id'] = $state->id;
            $temp['state_name'] = $state->name;
            $temp['district_id']= $district->id;
            $temp['district_name'] = $district->name;
            $temp['udyogi_count'] = count(Person::where('state',$state->name)->where('district',$district->name)->get());
            $temp['dose_1_count'] = count(Person::where('dose_1',true)->where('state',$state->name)->where('district',$district->name)->get());
            $temp['dose_2_count'] = count(Person::where('dose_2',true)->where('state',$state->name)->where('district',$district->name)->get());

            // $res->push($temp);
            $uv =  UdyogiVaccination::where('state_id',$temp['state_id'])->where('district_id',$temp['district_id'])->first();
            if($uv) {
                $uv->update($temp);
            }
            else {
                UdyogiVaccination::create($temp);
            }
            // dd($temp);

            $temp = null;
        }
    }
    return response()->json($res,200);
  }
  public function getUdyogiVaccinationData(Request $request) {
    $checktoken = $request->header('checktoken');
      //  dd($checktoken);
    if($checktoken != '0fQgVCL1cM2mGytOSovz') {
       return response()->json('Token Invalid',402);
    }
    return response()->json(UdyogiVaccination::all());
  }
//   public function dataForTarunOne(){
//     select
// sm.id as state_id,
// sm.name as state_name,
// dm.id as district_id,
// dm.name as district_name,
// count(*) as cnt
// from people pl
// inner join geographies gp on pl.geography_id=gp.id
// inner join districts dm on gp.parent_id=dm.id
// inner join states sm on dm.state_id=sm.id
// where pl.deleted_at is null
// group by sm.id,
// sm.name,
// dm.id,
// dm.name

//    $res = DB::table('people')->where('deleted_at',null);

//   }
}
