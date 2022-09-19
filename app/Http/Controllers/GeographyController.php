<?php

namespace App\Http\Controllers;

use App\Geography;
use Illuminate\Http\Request;
use App\Repositories\Repository\GeographyRepository;
use App\Http\Requests\GeographyRequest;
use App\City;
use App\State;
use App\Country;
use App\District;
use Validator;
use App\ServiceRateList;
use App\User;
use App\UserGeography;

class GeographyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private $repository;
    public function __construct(GeographyRepository $repository)
    {
        $this->repository = $repository;
    }
    public function filterGeography(Request $request)
    {
        $geographies = Geography::orderBy('name')->with('DmGeography');
        if(isset($request->name) && !empty($request->name)) 
        {   
            $geographies->where('name','like','%'.$request->name.'%');
        }
        if(isset($request->state) && !empty($request->state)) {
            $geographies->where('state','like','%'.$request->state.'%');

        }
        if(isset($request->district) && !empty($request->district)) {
            $geographies->where('district','like','%'.$request->district.'%');

        }
       // $geographies = $geographies->->get();
        // $res = collect();
        // foreach ($geographies as $geo) {
        //      $geo->parent = Geography::find($geo->parent_id);
        //      $res->push($geo); 
        // }
        if(isset($request['count']) && $request['count']) {
            $geo = $geographies->get();
            return response()->json(count($geo),200);
        }
        $offset = isset($request['skip']) ? $request['skip'] : 0 ;
        $chunk = isset($request['skip']) ? $request['limit'] : 999999;
        $geos = $geographies->skip($offset)->limit($chunk)->get();
        $res = collect();
        foreach ($geos as $geo) {
             $geo->parent = Geography::find($geo->parent_id);
             $geo->state_id = State::where('name',$geo->state)->first()->id;
             $res->push($geo); 
        }
        return response()->json($res,200);
        // return response()->json($res,200);
    }
    public function index(Request $request)
    {
        $geographies = Geography::with('DmGeography');
        if(isset($request->name) && !empty($request->name)) 
        {   
            $geographies->where('name','like','%'.$request->name.'%');
        }
        if(isset($request->state) && !empty($request->state)) {
            $geographies->where('state','like','%'.$request->state.'%');

        }
        if(isset($request->district) && !empty($request->district)) {
            $geographies->where('district','like','%'.$request->district.'%');

        }
        $geographies = $geographies->orderBy('name')->get();
        $res = collect();
        foreach ($geographies as $geo) {
             $geo->parent = Geography::find($geo->parent_id);
             $res->push($geo); 
        }
        return response()->json($res,200);
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
    public function store(GeographyRequest $request)
    {


        $state= State::find($request['state']);
        $request['state'] = $state->name;
        $data = $this->repository->create($request->all());
        $admins = User::where('is_super_admin',true)->get();
        foreach ($admins as $ad) {
            $temp['geography_id'] = $data->id;
            $temp['geography_type'] = $data->geography_type;
            $temp['user_id'] = $ad['id'];
            $ad->userGeographies()->create($temp);
        }

        return response()->json($data,200);
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Geography  $geography
     * @return \Illuminate\Http\Response
     */
    public function show(Geography $geography)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Geography  $geography
     * @return \Illuminate\Http\Response
     */
    public function edit(Geography $geography)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Geography  $geography
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Geography $geography)
    {
       $state= State::find($request['state']);
      $request['state'] = $state->name;
        return response()->json($this->repository->update($request->all(), $geography->id), 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Geography  $geography
     * @return \Illuminate\Http\Response
     */
    public function destroy(Geography $geography)
    {
        $geography = $this->repository->changeActiveStatus($geography->id);
        return response()->json($geography,200);
    }
    public function createGeography(Request $request)
    {
        $validation = Validator::make($request->all(),[
           'name' => 'required|string',
           'state_name' => 'required',
           'country_name' => 'required',
           'city_name' => 'required',
           'district_name' => 'required',
           'id' =>'required',
           'type' => 'required'
        ]);
        if($validation->fails()) {
           $errors = $validation->errors();
           return response()->json($errors,400);
        }
        // dd($request->all());
        $temp = [];
        if(isset($request['country_name'])) {
            $country = Country::where('name',$request['country_name'])->first();
            if($country) {
              // $temp['country_id'] = $country->id;
            }
            else{
                $tempc['name'] = $request['country_name'];
                $country= Country::create($tempc);
            }
           $temp['country_id'] = $country->id;   
        }
        if(isset($request['state_name'])) {
            $state = State::where('name',$request['state_name'])->first();
            if($state) {
              // $temp['country_id'] = $country->id;
                $srl = ServiceRateList::where('state_id',$state->id)->first();

                if($srl) {

                }
                else {
                    $tempratelist['professionals'] = 0;
                 $tempratelist['highly_skilled']= 0;
                 $tempratelist['skilled']= 0;
                 $tempratelist['semi_skilled']= 0;
                 $tempratelist['onskilled']= 0;
                 $tempratelist['onskilled_ratio']= 0;
                 $tempratelist['semi_skilled_ratio']= 0;
                 $tempratelist['skilled_ratio']= 0;
                 $tempratelist['highly_skilled_ratio']= 0;
                 $tempratelist['professionals_ratio']= 0;
                 $tempratelist['state_id']= $state->id;
                $srl = ServiceRateList::create($tempratelist);
                }
            }
            else{
                $temps['name'] = $request['state_name'];
                $temps['country_id'] = $temp['country_id'];  
                $state= State::create($temps);
                $tempratelist['professionals'] = 0;
                 $tempratelist['highly_skilled']= 0;
                 $tempratelist['skilled']= 0;
                 $tempratelist['semi_skilled']= 0;
                 $tempratelist['onskilled']= 0;
                 $tempratelist['onskilled_ratio']= 0;
                 $tempratelist['semi_skilled_ratio']= 0;
                 $tempratelist['skilled_ratio']= 0;
                 $tempratelist['highly_skilled_ratio']= 0;
                 $tempratelist['professionals_ratio']= 0;
                 $tempratelist['state_id']= $state->id;
                $srl = ServiceRateList::create($tempratelist);
            } 
           $temp['state_id'] = $state->id;  
        }  
        if(isset($request['district_name'])) {
            $district = District::where('name',$request['district_name'])->first();
            if($district) {
              // $temp['country_id'] = $country->id;
            }
            else{
                $tempd['name'] = $request['district_name'];
                 $tempd['state_id'] = $temp['state_id'];
                $district= District::create($tempd);
            }
            $temp['district_id'] = $district->id;  
        }
        if(isset($request['city_name'])) {
            $city = City::where('name',$request['city_name'])->first();
            if($city) {
              // $temp['country_id'] = $country->id;
            }
            else{
                $tempc['name'] = $request['city_name'];
                 $tempc['district_id'] = $temp['district_id'];
                $city= City::create($tempc);
            }  
          $temp['city_id'] = $city->id;
        }
        
        $request['remote_id'] = $request['id'];
        $request['parent_id'] = $temp['district_id'];
        $request['parent_pseudo_id'] = $temp['country_id']."-".$temp['state_id']."-".$temp['district_id'];
        $request['is_active'] = true;
        $request['district'] = District::find($temp['district_id']);
        $request['state'] = $request['state_name'];
        $request['district'] = $request['district_name'];
       // $request['district_id'] = 
        $geography = Geography::where('name',$request['name'])->first();
        if($geography) {
            
            $geography->update($request->only('name','type','parent_id','parent_pseudo_id','remote_id','is_active','district','state'));
        }
        else {
            $geography = Geography::create($request->only('name','type','parent_id','parent_pseudo_id','remote_id','is_active','district','state'));
        }
        $admins = User::where('is_super_admin',true)->get();
        foreach ($admins as $ad) {
            $temp['geography_id'] = $geography->id;
            $temp['geography_type'] = $geography->geography_type;
            $temp['user_id'] = $ad['id'];
            $ad->userGeographies()->create($temp);
        }
        return response()->json($geography,200);
    }
    // public function getStats(Request $request)
    // {
    //     $res = collect();
    //     $res['no_of_active_geography'] = Geography::getActiveGeography($request->all());
    //     $res['no_of_active_dm'] = DrishteeMitra::getActiveMitra($request->all());
    //     $res['no_of_active_person'] = Person::getActivePerson($request->all());
    //     $res['total_no_of_tejas_product']= Product::getTejasProduct($request->all());
    //     $res['total_no_of_product'] = Product::getProduct($request->all());
    //     return response()->json($res,200);  
    // }

    public function deleteGeography(Request $request,$id)
    {
        
        // $geography = Geography::find($id);
        // $usersG = $geography->userGeographies;
        // foreach ($usersG as $userG) {
        //     $ug = UserGeography::find($userG->id);
        //     $ug->destroy($userG->id);
        // }

        // $geography->destroy($id);
        // return response()->json(true,201); 


        // $geography = Geography::find($id);
        // $users = $geography->userGeographies;
        // foreach ($users as $user) {
        //     $user->geography_id = 16;
        //     $user->save();
        // }
        // $geography->destroy($id);
        // return response()->json(true,201); 

        // return response()->json($geography->load('DmGeography','personProduct','personService','userGeographies','disputes','barters'),200);


        $geography = Geography::find($id);
        if($geography) {
            $dmgeo = $geography->DmGeography;
            $pproducct = $geography->personProduct;
            $pservice = $geography->personService;
            $ugeo = $geography->userGeographies;
            $disgeo = $geography->disputes;
            $bgeo = $geography->barters;
            if(count($dmgeo) > 0 || count($pproducct) > 0 || count($pservice) > 0 || count($disgeo) > 0 || count($bgeo) > 0) {
                return response()->json(['error'=>'you can not delete this Geography'],400);
            }
            else {
                $usersG = $geography->userGeographies;
                foreach ($usersG as $userG) {
                    $ug = UserGeography::find($userG->id);
                    $ug->destroy($userG->id);
                }
                $geography->destroy($id);
                return response()->json(true,201); 
            }
        }
        return response()->json(['error'=>'Not Found'],404);   
    }
    public function updateGeographyExternal(Request $request)
    {
        $validation = Validator::make($request->all(),[
           'new_name' => 'required|string',
           'old_name' => 'required|string',
           
           'state_name' => 'required',
           'country_name' => 'required',
           'city_name' => 'required',
           'district_name' => 'required',
           'id' =>'required',
           'type' => 'required'
        ]);
        if($validation->fails()) {
           $errors = $validation->errors();
           return response()->json($errors,400);
        }
        $checktoken = $request->header('checktoken');
      //  dd($checktoken);
        if($checktoken != '0fQgVCL1cM2mGytOSovz') {
           return response()->json('Token Invalid',402);
        }
       $geography = Geography::where('name',$request['old_name'])->first();
       if($geography) {
        $temp = [];
        if(isset($request['country_name'])) {
            $country = Country::where('name',$request['country_name'])->first();
            if($country) {
              // $temp['country_id'] = $country->id;
                $tempcountry['name'] = $request['country_name'];
                $country->update($tempcountry);
            }
            else{
                $tempc['name'] = $request['country_name'];
                $country= Country::create($tempc);
            }
           $temp['country_id'] = $country->id;   
        }
        if(isset($request['state_name'])) {
            $state = State::where('name',$request['state_name'])->first();
            if($state) {
              // $temp['country_id'] = $country->id;
                $tempstate['name'] = $request['state_name'];
                $state->update($tempstate);
                $srl = ServiceRateList::where('state_id',$state->id)->first();

                if($srl) {
                 $tempratelist['professionals'] = $srl ? $srl->professionals : 0;
                 $tempratelist['highly_skilled']= $srl ? $srl->highly_skilled : 0;
                 $tempratelist['skilled']= $srl ? $srl->skilled : 0;
                 $tempratelist['semi_skilled']= $srl ? $srl->semi_skilled : 0;
                 $tempratelist['onskilled']= $srl ? $srl->onskilled : 0;
                 $tempratelist['onskilled_ratio']= $srl ? $srl->onskilled_ratio : 0;
                 $tempratelist['semi_skilled_ratio']= $srl ? $srl->semi_skilled_ratio : 0;
                 $tempratelist['skilled_ratio']= $srl ? $srl->skilled_ratio : 0;
                 $tempratelist['highly_skilled_ratio']= $srl ? $srl->highly_skilled_ratio : 0;
                 $tempratelist['professionals_ratio']=$srl ? $srl->professionals_ratio : 0 ;
                 $tempratelist['state_id']= $state->id;
                $srl->update($tempratelist);
                }
                else {
                    $tempratelist['professionals'] = 0;
                 $tempratelist['highly_skilled']= 0;
                 $tempratelist['skilled']= 0;
                 $tempratelist['semi_skilled']= 0;
                 $tempratelist['onskilled']= 0;
                 $tempratelist['onskilled_ratio']= 0;
                 $tempratelist['semi_skilled_ratio']= 0;
                 $tempratelist['skilled_ratio']= 0;
                 $tempratelist['highly_skilled_ratio']= 0;
                 $tempratelist['professionals_ratio']= 0;
                 $tempratelist['state_id']= $state->id;
                $srl = ServiceRateList::create($tempratelist);
                }
            }
            else{
                $temps['name'] = $request['state_name'];
                $temps['country_id'] = $temp['country_id'];  
                $state= State::create($temps);
                $tempratelist['professionals'] = 0;
                 $tempratelist['highly_skilled']= 0;
                 $tempratelist['skilled']= 0;
                 $tempratelist['semi_skilled']= 0;
                 $tempratelist['onskilled']= 0;
                 $tempratelist['onskilled_ratio']= 0;
                 $tempratelist['semi_skilled_ratio']= 0;
                 $tempratelist['skilled_ratio']= 0;
                 $tempratelist['highly_skilled_ratio']= 0;
                 $tempratelist['professionals_ratio']= 0;
                 $tempratelist['state_id']= $state->id;
                $srl = ServiceRateList::create($tempratelist);
            } 
           $temp['state_id'] = $state->id;  
        }  
        if(isset($request['district_name'])) {
            $district = District::where('name',$request['district_name'])->first();
            if($district) {
                $tempdistrict['name'] = $request['district_name'];
                $district->update($tempdistrict);
              // $temp['country_id'] = $country->id;

            }
            else{
                $tempd['name'] = $request['district_name'];
                 $tempd['state_id'] = $temp['state_id'];
                $district= District::create($tempd);
            }
            $temp['district_id'] = $district->id;  
        }
        if(isset($request['city_name'])) {
            $city = City::where('name',$request['city_name'])->first();
            if($city) {
               $tempcity['name'] = $request['city_name'];
               $city->update($tempcity); 
              // $temp['country_id'] = $country->id;
            }
            else{
                $tempc['name'] = $request['city_name'];
                 $tempc['district_id'] = $temp['district_id'];
                $city= City::create($tempc);
            }  
          $temp['city_id'] = $city->id;
        }
        
        $request['remote_id'] = $request['id'];
        $request['parent_id'] = $temp['district_id'];
        $request['parent_pseudo_id'] = $temp['country_id']."-".$temp['state_id']."-".$temp['district_id'];
        $request['is_active'] = true;
        $request['district'] = District::find($temp['district_id']);
        $request['name'] = $request['new_name'];
        $request['state'] = $request['state_name'];
        $request['district'] = $request['district_name'];
       // $request['district_id'] = 
    //    $geography = Geography::where('name',$request['name'])->first();

      //  if($geography) {
            
            $geography->update($request->only('name','type','parent_id','parent_pseudo_id','remote_id','is_active','district','state'));
       // }
       // else {
         //   $geography = Geography::create($request->only('name','type','parent_id','parent_pseudo_id','remote_id','is_active','district','state'));
       // }
        return response()->json($geography,200);
       }
       return response()->json(['error'=>'Geography_Not_Found'],404);
    }
    public function geographyAddInSuperAdmin()
    {
        $geographies = Geography::all();
        foreach ($geographies as $geo) {
            $users = User::where('is_super_admin',true)->get();
            foreach ($users as $user) {
                $temp['geography_id'] = $geo->id;
                    $temp['geography_type'] = $geo->type;
                    $temp['user_id'] = $user->id;
                $ugs = UserGeography::where('user_id',$user->id)->where('geography_id',$geo->id)->get();
              //  foreach ($ugs as $ug) {
                  if(count($ugs) ==0 ) {
                    UserGeography::create($temp);
                    
                }
                
            }
        }
    }
}
