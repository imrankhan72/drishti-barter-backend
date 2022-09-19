<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Hash;
use App\Notifications\ActivateNotification;
use Auth;
use App\Ledger;
use Mail;
use App\Mail\AdminCreateMail;
use App\UserGeography;
use Validator;
use App\Geography;
use App\LedgerTransaction;
use Carbon\Carbon;
use Log;
class UserController extends Controller
{
    
    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\User $user
     * do store user and Ledger
     */
    public function store(Request  $request){
        $validation = Validator::make($request->all(),[
           'first_name' => 'required',
           'last_name'  =>'required',
           'email'      => 'required|unique:users,email',
           'mobile'     => 'required|unique:users,mobile',
           'is_super_admin' => 'required',
           'is_management'  => 'required',
           'geographies.*' => 'required_if:is_super_admin, == , true|array',

        ]);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,400);
        }
        if(!$request->remote_id){
            $request['remote_id'] = null;
        }
        $user = $request->only(['first_name','last_name','email','mobile','is_super_admin','remote_id']);
        $password = $request['password'];
        if(isset($request['password'])) {
            $request['password'] = Hash::make($request->password);
            $user = $request->only(['first_name','last_name','password','email','mobile','is_super_admin','is_management','remote_id']);
        }
        $user = User::create($user);
        if($request['geographies']) {
        foreach ($request['geographies'] as $ug) {
          // $tem['geography_id'] = 
        $u_geography = $user->userGeographies()->create($ug);

        }    
        }
        
        $user->createUserTransaction('Success',0);
        $ledger = Ledger::where('ledger_id',$user->id)->first();
        $user->ledger_id = $ledger->id;
        $user->save();
        $template_id = 1207161761233797199;

        Mail::to($user->email)->send(new AdminCreateMail($user,$password));
        sendSMS('Welcome to Miri Market Barter. Your admin account has been successfully created. Login here http://drishteeapp.cobold.xyz/ with email and Password.'.$request['email'].' '.$password,$user->mobile,$template_id);
        return response()->json($user->load('userGeographies','userProducts'), 201);
    }

    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User $user_id
     * @return \App\User $user
     * do update user details and delete old UserGeographyservice and create new
     */
    public function update(Request $request,$user_id){
        $validation = Validator::make($request->all(),[
            'first_name' => 'required',
            'last_name'  =>'required',
            'email'      => 'required|unique:users,email,'.$user_id,
            'mobile'     => 'required',
            'is_super_admin'  => 'required|boolean',
            'is_management'   => 'required|boolean',
            'geographies.*' => 'required|array'
        ]);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,400);
        }

        if(!$request->remote_id){
            $request['remote_id'] = null;
        }

        $user = User::find($user_id);
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->mobile = $request->mobile;
        $user->email = $request->email;
        $user->is_super_admin = $request->is_super_admin;
        $user->is_management = $request->is_management;
        $user->remote_id = $request->remote_id;
        $user->save();

        $userGeography = UserGeography::where('user_id',$user_id)->delete();
        
        foreach ($request->geographies as $geography) {
            $userGeography = new UserGeography();
            $userGeography->user_id = $user_id;
            $userGeography->geography_id = $geography['geography_id'];
            $userGeography->geography_type = $geography['geography_type'];
            $userGeography->save();   
        }
        return response()->json($user->load('userGeographies.geography','userProducts')); 
        
    }


    /**
     *
     * @return \App\User $users
     * do return list of users with userGeographies
     */
    public function index(){
        $users = User::orderBy('is_super_admin','DESC')->orderBy('first_name')->get();
        return response()->json($users->load('userGeographies.geography','userProducts.user','userProducts.product.units','ledger'),200);
    }
    public function filterUser(Request $request)
    {
      $users = User::orderBy('is_super_admin','DESC')->orderBy('first_name')->with('userGeographies.geography','userProducts.user','userProducts.product.units','ledger');
        // $geography_ids = $request['geography_ids'];
        // $dms->whereHas('dmGeography',function($query) use($geography_ids) {
                // $query->whereIn('geography_id', $geography_ids);
            // });

        if(isset($request['filters']['first_name']) && !empty($request['filters']['first_name'])) 
        {
            $users->where('first_name','like','%'.$request['filters']['first_name'].'%');
        }
        if(isset($request['filters']['last_name']) && !empty($request['filters']['last_name'])) 
        {
            $users->where('last_name','like','%'.$request['filters']['last_name'].'%');
        }

        if(isset($request['filters']['email']) && !empty($request['filters']['email'])) {
            $users->where('email','like','%'.$request['filters']['email'].'%');

        }
        if(isset($request['filters']['mobile']) && !empty($request['filters']['mobile'])) {
            $users->where('mobile','like','%'.$request['filters']['mobile'].'%');

        }
        // if(isset($request['filters']['added_by']) && !empty($request['filters']['added_by'])) {
        //     $dms->where('added_by',$request['filters']['added_by']);
        // }
        
        // if(isset($request['filters']['is_csp']) && (false ==$request['filters']['is_csp'] || 
        //     true == $request['filters']['is_csp'])) {
        //     $dms->where('is_csp', $request['filters']['is_csp']);
        // }
        // if(isset($request['filters']['is_vaani']) && (false ==$request['filters']['is_vaani'] || 
        //     true == $request['filters']['is_vaani'])) {
        //     $dms->where('is_csp', $request['filters']['is_vaani']);
        // }
        // if(isset($request['filters']['geography_id']) && !empty($request['filters']['geography_id'])) {
        //     $geography_id = $request['filters']['geography_id'];
        //     $dms->whereHas('dmGeography',function($query) use($geography_id) {
        //         $query->where('geography_id', $geography_id);
        //     });
        // }
        if(isset($request['count']) && $request['count']) {
            $us = $users->get();
            return response()->json(count($us),200);
        }
        $offset = isset($request['skip']) ? $request['skip'] : 0 ;
        $chunk = isset($request['skip']) ? $request['limit'] : 999999;
        $usr = $users->skip($offset)->limit($chunk)->get();
        
        return response()->json($usr,200);
    }
    public function show($id)
    {
        $user = User::find($id);
        if($user) {
// <<<<<<< HEAD
//             return response()->json($user->load('userGeographies.geography','userProducts.user','userProducts.product.units','ledger'),200);
// =======
            return response()->json($user->load('userGeographies.geography','userProducts.user','userProducts.product.units','ledgers','ledgerTransactions','ledger'),200);
        }
        return response()->json(['error'=>'Not Found'],404);
    }
    public function getUserLedgerTransaction($id){
      $user = User::find($id);
      $startdate = Carbon::now()->subDays(30)->format('Y/m/d');
      $enddate = Carbon::tomorrow()->format('Y/m/d');

      $transaction = LedgerTransaction::where('ledger_id',$user->ledger_id)->where('created_at','>=',$startdate)->where('created_at','<=',$enddate)->get();
      return response()->json($transaction,200);       
    }
    public function ledgerfilter(Request $request, $user_id){
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
        $user = User::find($user_id);
        $ledger = Ledger::find($user->ledger_id);

        $transaction = LedgerTransaction::where('ledger_id',$ledger->id)->where('created_at','>=',$startdate)->where('created_at','<=',$enddate)->get();
        return response()->json($transaction,200);
    }
    public function test()
    {
        $user = Auth::User();
        $user->notify(new ActivateNotification());
        return response()->json(true,200);
    }

    /**
     *
     * @param  \App\User $id
     * @return \App\LedgerTransaction $user
     * do return all transation of user
     */
    public function getUserTrasnaction($id){
      $user = User::find($id);
      return response()->json($user->ledgerTransactions,200);

    }

    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User $user_id
     * @return \App\User $user
     * do update user details and delete old UserGeographyservice and create new
     */
    public function updateUser(Request $request, $user_id){
        $validation = Validator::make($request->all(),[
            'first_name' => 'required',
            'last_name'  =>'required',
            'email'      => 'required|unique:users,email,'.$user_id,
            'mobile'     => 'required',
            'is_super_admin'  => 'required|boolean',
            'is_management'   => 'required|boolean',
            'geographies.*' => 'required|array'
        ]);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,400);
        }
        if(!$request->remote_id){
            $request['remote_id'] = null;
        }

        $user = User::find($user_id);
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->mobile = $request->mobile;
        $user->email = $request->email;
        $user->is_super_admin = $request->is_super_admin;
        $user->is_management = $request->is_management;
        $user->remote_id = $request->remote_id;
        $user->save();

        $userGeography = UserGeography::where('user_id',$user_id)->delete();
        
        foreach ($request->geographies as $geography) {
            $userGeography = new UserGeography();
            $userGeography->user_id = $user_id;
            $userGeography->geography_id = $geography['geography_id'];
            $userGeography->geography_type = $geography['geography_type'];
            $userGeography->save();   
        }
        return response()->json($user->load('userGeographies.geography')); 
    }

    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User $id
     * @return \App\User $user
     * do update user status
     */
    public function statusChange(Request $request,$id){
        $user = User::find($id);
        if($user) {
            $user->update($request->only('status'));
            return response()->json($user,201);
        }
        return response()->json(['error'=>'Not Found'],404);
    }

    /**
     *
     * @return \App\User $user
     * do get list of deactive users
     */
    public function getDeactivatedUser(){
        $users = User::where('status','Deactivate')->get();
        return response()->json($users,200);
    }
    public function storeUserFromExternal(Request $request)
    {
        $validation = Validator::make($request->all(),[
           'first_name' => 'required',
           'last_name'  =>'required',
           'email'      => 'required|email',
           'mobile'     => 'required',
           'geographies.*' => 'required|array',
           'password'      => 'required'

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
        $request['is_super_admin'] = false;
        if(!$request->remote_id){
            $request['remote_id'] = null;
        }

        $user = $request->only(['first_name','last_name','email','mobile','is_super_admin','remote_id']);
        Log::info("store user external".$request);
        $password = $request['password'];
        if(isset($request['password'])) {
            $request['password'] = Hash::make($request->password);
            $user = $request->only(['first_name','last_name','password','email','mobile','is_super_admin']);
        }

        $user = User::where('email',$request['email'])->where('mobile',$request['mobile'])->first();
       // dd($user);

        foreach ($request['geographies'] as $rg) {
              $rg = Geography::where('name',$rg['name'])->first();
              if(!$rg) return response()->json('Geography Not Found.',400);
        }
        if($user) {
            $user->update($request->only(['first_name','last_name','password','email','mobile','is_super_admin','remote_id']));
            if($user->userGeographies) {
                foreach ($user->userGeographies as $uge) {
                   $ugeo = UserGeography::find($uge->id);
                   $ugeo->destroy($ugeo->id); 
                }
            }
            // $tempres = array();
            foreach ($request['geographies'] as $rg) {
              $rg = Geography::where('name',$rg['name'])->first();
              // $temp['geography_id'] = $rg->id;
              // $temp['geography_type'] = $rg->type;
              // array_push($tempres,$temp);  
             $u_geography = $user->userGeographies()->create(['geography_type'=>$rg->type,'geography_id'=>$rg->id]);

            }
           // dd(json_encode($tempres,true)); 

        } 
        else {
        $user = User::create($request->only(['first_name','last_name','password','email','mobile','is_super_admin','remote_id']));
        // $tempres = array();
            foreach ($request['geographies'] as $rg) {
              $reg = Geography::where('name',$rg['name'])->first();
               
             $u_geography = $user->userGeographies()->create(['geography_type'=>$reg->type,'geography_id'=>$reg->id]);

            }
        $user->createUserTransaction('Success',0);
        $ledger = Ledger::where('ledger_id',$user->id)->first();
        $user->ledger_id = $ledger->id;
        $user->save();
        $template_id = 1207161761233797199;
        //Mail::to($user->email)->send(new AdminCreateMail($user,$password));
        sendSMS('Welcome to Miri Market Barter. Your admin account has been successfully created. Login here http://drishteeapp.cobold.xyz/ with email and Password.'.$request['email'].' '.$password,$user->mobile,$template_id);    
        }
        
        // if($request['geographies']) {
        // foreach ($request['geographies'] as $ug) {
        //   // $tem['geography_id'] = 
        // $u_geography = $user->userGeographies()->create($ug);

        // }    
        // }
        
        
        return response()->json($user->load('userGeographies'), 201);
    }
    public function deleteUser(Request $request, $id){
        $user = User::find($id);
        // $user->destroy(6);
        // $ug = UserGeography::find(3148);
        // $ug->destroy(3148);

        // return response()->json($user->load('DmGeography','userGeographies','sellRequestComments','buyRequestComments','lpRequestFromAdmin','approvedBySuperAdmin','personBans'),200);

        if($user && !$user->is_super_admin) {
            $dmgeo = $user->DmGeography;
            $ugeo = $user->userGeographies;
            $src = $user->sellRequestComments;
            $brc = $user->buyRequestComments;
            $lrfa = $user->lpRequestFromAdmin;
            $absa = $user->approvedBySuperAdmin;
            if(count($dmgeo) > 0 || count($src) > 0 || count($brc) > 0 || count($lrfa) > 0 || count($absa) > 0) {
                return response()->json(['error'=>'you can not delete this User'],400);
            }else{
                $user->destroy($id);
                return response()->json(true,200);
            }
        }else{
            return response()->json(['error'=>'User Not Found Or This user is super admin'],404);
        }
    }


    public function deleteUserExternal(Request $request)
    {
       $validation = Validator::make($request->all(),[
           'email'  => 'required|exists:users,email',
           'mobile' => 'required|exists:users,mobile'
       ]);
       if($validation->fails()) {
        $errors = $validation->errors();
        return response()->json($errors,401);
       }
       $checktoken = $request->header('checktoken');
        if($checktoken != '0fQgVCL1cM2mGytOSovz') {
           return response()->json('Token Invalid',402);
        }
        $user = User::where('email',$request['email'])->where('mobile',$request['mobile'])->first();
       if($user && !$user->is_super_admin) {
            $dmgeo = $user->DmGeography;
            $ugeo = $user->userGeographies;
            $src = $user->sellRequestComments;
            $brc = $user->buyRequestComments;
            $lrfa = $user->lpRequestFromAdmin;
            $absa = $user->approvedBySuperAdmin;
            if(count($dmgeo) > 0 || count($src) > 0 || count($brc) > 0 || count($lrfa) > 0 || count($absa) > 0) {
                return response()->json(['error'=>'you can not delete this User'],400);
            }else{
                $user->destroy($id);
                return response()->json(true,200);
            }
        }else{
            return response()->json(['error'=>'User Not Found Or This user is super admin'],404);
        }
    }
}
