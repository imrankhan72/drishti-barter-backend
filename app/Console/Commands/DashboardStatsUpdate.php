<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Person;
use App\Product;
use App\DrishteeMitra;
use App\PersonService;
use App\Geography;
use App\Barter;
use App\DashboardStats;

class DashboardStatsUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dashboard:stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for update dashboard stats';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $startdate = null;
        $enddate = null;
        $dt = Carbon::now();
        $geography = Geography::count();
        $dm = DrishteeMitra::all();
        $persons = Person::all();
        $products = Product::count();
        $pService = PersonService::count();
        $openBarters = null;
        $vaccinated = Person::where('vaccinated',true)->get();
        $d1_vaccinated = Person::where('dose_1',true)->get();
        $d2_vaccinated = Person::where('dose_2',true)->get();
        $csp_count = 0;
        $mitra_count = 0;
        $ceep_count = 0;
        $others_count = 0;
        $batters = Barter::where('status','Completed')->get();
        $battersCount = count($batters);
        $openBarters = Barter::where('status','Open')->get();
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
        $lpinaccount = 0;
        $nolpinaccount = 0;
        foreach ($persons as $key) {
            if(count($key->personProducts) == 0){
                $personWithNoProduct++;
            }
            if($key->ledger && $key->ledger->balance==0) {
                $nolpinaccount++;
            }
            else {
                $lpinaccount++;
            }
        }
        
        $avgProduct = round($products/count($persons),1);

        $dmWithNoPerson = 0;
        foreach ($dm as $key) {
            if(!count($key->personAddedBy)){
                $dmWithNoPerson++;
            }
                $personsg = Person::where('dm_id',$key->id)->get();
            if($key->type == 'CSP') {
              if(count($personsg) > 1 ) {
                $csp_count += 1;
                // dd($csp_count);
              }
            }
            else if($key->type =='Mitra') {
                if(count($personsg) > 1 ) {
                $mitra_count += 1;
              }
            }
           else if($key->type =='CEEP') {
             if(count($personsg) > 1 ) {
                $ceep_count += 1;
              }
           }
           else {
            if(count($personsg) > 1 ) {
                $others_count += 1;
              } 
           }
        }
        $avgDm = round(count($persons)/count($dm),1);
        $avgServices = round(count($persons)/$pService,1);
        // $persons = Person::all();
        // $lpinaccount = 0;
        // $nolpinaccount = 0; 
        // foreach ($persons as $person) {
        //     if($person->ledger && $person->ledger->balance==0) {
        //         $nolpinaccount++;
        //     }
        //     else {
        //         $lpinaccount++;
        //     }
        // }
        $dashboardstats = DashboardStats::first();
        // dd("hee");
        if($dashboardstats) {
            $dashboardstats->vatika = $geography;
            $dashboardstats->mitras = count($dm);
            $dashboardstats->persons= count($persons);
            $dashboardstats->products = $products;
            $dashboardstats->completed_barters = $battersCount;
            $dashboardstats->completed_barter_lp=  $batterslp;
            $dashboardstats->completed_barter_geo= count($bGID);
            $dashboardstats->open_barters = count($openBarters);
            $dashboardstats->open_barter_lp = $openBarterLp;
            $dashboardstats->open_barter_geo = count($openBGID);
            $dashboardstats->tejas_products = $tejasProduct;
            $dashboardstats->average_products =  $avgProduct;
            $dashboardstats->average_services = $avgServices;
            $dashboardstats->producers_with_no_product= $personWithNoProduct;
            $dashboardstats->average_no_of_people_with_dm =  $avgDm;
            $dashboardstats->dm_with_no_people = $dmWithNoPerson;
            $dashboardstats->producers_with_lp_in_account = $lpinaccount;
            $dashboardstats->producers_with_no_lp_in_account = $nolpinaccount;
            $dashboardstats->csp_count = $csp_count;
            $dashboardstats->mitra_count = $mitra_count;
            $dashboardstats->ceep_count = $ceep_count;
            $dashboardstats->others_count = $others_count;
            $dashboardstats->vaccinated = count($vaccinated);
            $dashboardstats->d1_vaccinated = count($d1_vaccinated);
            $dashboardstats->d2_vaccinated = count($d2_vaccinated);
            
            $dashboardstats->save();
        }
        else {
            $data['vatika'] = $geography;
            $data['mitras'] = count($dm);
            $data['persons'] = count($persons);
            $data['products'] = $products;
            $data['completed_barters'] = $battersCount;
            $data['completed_barter_lp'] =  $batterslp;
            $data['completed_barter_geo'] = count($bGID);
            $data['open_barters'] = count($openBarters);
            $data['open_barter_lp'] = $openBarterLp;
            $data['open_barter_geo'] = count($openBGID);
            $data['tejas_products'] = $tejasProduct;
            $data['average_products'] = $avgProduct;
            $data['average_services'] = $avgServices;
            $data['producers_with_no_product'] = $personWithNoProduct;
            $data['average_no_of_people_with_dm'] = $avgDm;
            $data['dm_with_no_people'] = $dmWithNoPerson;
            $data['producers_with_lp_in_account'] = $lpinaccount;
            $data['producers_with_no_lp_in_account'] = $nolpinaccount;
            $data['csp_count'] = $csp_count;
            $data['mitra_count'] = $mitra_count;
            $data['ceep_count'] = $ceep_count;
            $data['others_count'] = $others_count;
            $data['vaccinated'] = count($vaccinated);
            $data['d1_vaccinated'] = count($d1_vaccinated);
            $data['d2_vaccinated'] = count($d2_vaccinated);
            DashboardStats::create($data);
        }
       // return response()->json(
            // ['dm'=>count($dm),
            // 'geography'=>$geography,
            // 'persons'=> count($persons),
            // 'products'=>$products,
            // 'batters'=> $battersCount,
            // 'batterslp'=>$batterslp,
            // 'openbarterlps'=> $openBarterLp,
            // 'openbarterGC'=> count($openBGID),
            // 'openBarterCount' => count($openBarters),
            // 'batterGC'=> count($bGID),
            // 'tejasProduct'=>$tejasProduct,
            // 'avgProduct'=>$avgProduct,
            // 'personsWithNoProduct'=>$personWithNoProduct,
            // 'dmWithNoPerson'=>$dmWithNoPerson ,
            // 'avgDm'=>$avgDm,
            // 'avgServices'=>$avgServices,
            //  'nolpinaccount'=>$nolpinaccount,
            //  'lpinaccount'=>$lpinaccount],200);
    }
}
