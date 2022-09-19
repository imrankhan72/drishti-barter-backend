<?php

namespace App\Http\Controllers;

use App\Vaccination;
use Illuminate\Http\Request;
use Validator;
use Storage;
use File;
use Carbon\Carbon;
use Log;
use App\Geography;
use App\DrishteeMitra;
use App\State;
use Rap2hpoutre\FastExcel\FastExcel;

class VaccinationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function filterVaccination(Request $request){
        $vcs = Vaccination::with('addedBy','person.personPersonalDetails','geography')->orderBy('created_at','DESC');
        $geography_ids = $request['geography_ids'];
        $vcs->whereIn('geography_id',$geography_ids);
        if(isset($request['filters']['added_by']) && !empty($request['filters']['added_by'])) 
        {
            $vcs->where('added_by',$request['filters']['added_by']);
                 
        }

        if(isset($request['filters']['name']) && !empty($request['filters']['name'])) {

            $name = $request['filters']['name'];
            $vcs->whereHas('person',function($query) use($name) {
                $query->where('first_name','like','%'.$name.'%')
                ->whereOr('middle_name','like','%'.$name.'%')
                ->whereOr('last_name','like','%'.$name.'%');
            });
            //$dms->where('email','like','%'.$request['filters']['email'].'%');

        }
        // if(isset($request['filters']['geography_id']) && !empty($request['filters']['email'])) {
        //     $dms->where('email','like','%'.$request['filters']['email'].'%');

        // }
        if(isset($request['filters']['state']) && !empty($request['filters']['state'])) {
            $st = $request['filters']['state'];
            $vcs->whereHas('geography',function($query) use($st){
              $query->where('state',$st);
            });
        }
        if(isset($request['filters']['district']) && !empty($request['filters']['district'])) {
            $dis = $request['filters']['district'];
            $vcs->whereHas('geography',function($query) use($dis){
              $query->where('district',$dis);
            });
        }
        if(isset($request['filters']['dose_1_complete']) && ( $request['filters']['dose_1_complete'] == true || $request['filters']['dose_1_complete'] == false)) {
            $vcs->where('dose_1_complete',$request['filters']['dose_1_complete']);

        }
         if(isset($request['filters']['dose_2_complete']) && ( $request['filters']['dose_2_complete'] == true || $request['filters']['dose_2_complete'] == false) ){
         //  dd("hr");
            $vcs->where('dose_2_complete',$request['filters']['dose_2_complete']);

        }

        if(isset($request['filters']['vaccine_name']) && !empty($request['filters']['vaccine_name'])) {
            $vcs->where('vaccine_name',$request['filters']['vaccine_name']);
        }
        
        // if(isset($request['filters']['is_csp']) && (false ==$request['filters']['is_csp'] || 
        //     true == $request['filters']['is_csp'])) {
        //     $dms->where('is_csp', $request['filters']['is_csp']);
        // }
        
        // if(isset($request['filters']['geography_id']) && !empty($request['filters']['geography_id'])) {
        //     $geography_id = $request['filters']['geography_id'];
        //     $dms->whereHas('dmGeography',function($query) use($geography_id) {
        //         $query->where('geography_id', $geography_id);
        //     });
        // }
        if(isset($request['count']) && $request['count']) {
            $vacc = $vcs->get();
            return response()->json(count($vacc),200);
        }
        $offset = isset($request['skip']) ? $request['skip'] : 0 ;
        $chunk = isset($request['skip']) ? $request['limit'] : 999999;
        $vacc = $vcs->skip($offset)->limit($chunk)->get();
        $res = collect();
        foreach ($vacc as $vac) {
            $geography = Geography::find($vac->geography_id);
            $vac->state = $geography->state ? $geography->state: '';
            $vac->district = $geography->district ? $geography->district:'';
            $res->push($vac); 
        }
        return response()->json($res,200);  
    }
    public function index()
    {
        $vc = Vaccination::all()->load('person');
        return response()->json($vc,200);
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
        $validation = Validator::make($request->all(),[
            'reg_id'                    =>'required:unique:vaccinations,reg_id',
            'added_by'                  =>'required|exists:drishtree_mitras,id',
            'person_id'                 => 'required|exists:people,id',
            'person_id'                 => 'required|unique:vaccinations,person_id',
            'geography_id'              =>  'required|exists:geographies,id',
            'vaccine_name'              => 'required',
            'dose_1_date'               => 'sometimes',
            'dose_2_date'               => 'sometimes',
            'dose_1_complete'           =>'sometimes',
            'dose_2_complete'           =>'sometimes',
            'gender'                    =>'required',
            'dose_1_place'              => 'sometimes',
            'dose_2_place'              => 'sometimes',
            'is_dose_1'                 => 'boolean',
            'is_done_2'                 => 'boolean'


        ]);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,400);
        }
        $vaccination = Vaccination::create($request->all());
        return response()->json($vaccination->load('person','addedBy','geography'),200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Vaccination  $vaccination
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $vc = Vaccination::find($id);
        return response()->json($vc->load('person','addedBy','geography'),200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Vaccination  $vaccination
     * @return \Illuminate\Http\Response
     */
    public function edit(Vaccination $vaccination)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Vaccination  $vaccination
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validation = Validator::make($request->all(),[
            'reg_id'                    =>'required|unique:vaccinations,reg_id,'.$id,
            'added_by'                  =>'required|exists:drishtree_mitras,id',
            'person_id'                 => 'required|exists:people,id',
            'person_id'                 => 'required|unique:vaccinations,person_id,'.$id,
            'geography_id'              =>  'required|exists:geographies,id',
            'vaccine_name'              => 'required',
            'dose_1_date'               => 'sometimes',
            'dose_2_date'               => 'sometimes',
            'dose_1_complete'           =>'sometimes',
            'dose_2_complete'           =>'sometimes',
            'gender'                    =>'required',
            'dose_1_place'              => 'sometimes',
            'dose_2_place'              => 'sometimes',
            'is_dose_1'                 => 'required|boolean',
            'is_dose_2'                 => 'required|boolean' 
        ]);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,400);
        }
       $vc = Vaccination::find($id);
       if($vc) {
       $vc->update($request->all()); 
       return response()->json($vc->load('person','addedBy','geography'),200);
       }
       return response()->json(['error'=>'Not Found'],404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Vaccination  $vaccination
     * @return \Illuminate\Http\Response
     */
    public function destroy(Vaccination $vaccination)
    {
        //
    }
    public function uploadPDF(Request $request,$id){
        // dd($request['file']);
       //$this->authorize('create',StaffBasicDetails::class);
       $validation = Validator::make($request->all(),[
            'file' => 'required|file|mimes:pdf|max:10000'
        ]);
        if($validation->fails()){
            $errors = $validation->errors();
            return response()->json($errors, 400);
        }
       $vc = Vaccination::find($id);
     //  $dp = DMProfile::where('dm_id',$dm->id)->first();
       // dd($dp);
       // Log::info("req".$request['dose_1_complete']);
       $vc->dose_1_complete = true;
       $vc->save();
       $file = $request['file']->getClientOriginalName();
       $extension = $request['file']->getClientOriginalExtension(); 
       // Log::info("ell".$extension);
       // Log::info("file".$file);
       $filename = pathinfo($file, PATHINFO_FILENAME);
       $dttime = Carbon::now()->format('YmdHis');
       $originalName = $filename.$dttime.'.'.$extension; 
       $cover = $request->file('file');
       Storage::disk('public')->put($originalName,File::get($cover));
       $request['dose_1_certificate_name'] = $originalName;
       $request['dose_1_certificate_path'] = Storage::disk('public')->url($originalName);
       $request['certificate_dose_1_upload_date'] = Carbon::now();
       // Log::info("him".$request['certificate_dose_1_upload_date']);
       $vc->update($request->only(['dose_1_certificate_name','dose_1_certificate_path','certificate_dose_1_upload_date']));
       
       return response()->json($vc,201); 
    }
    public function uploadPDFDose2(Request $request,$id){
        // dd($request['file']);
       //$this->authorize('create',StaffBasicDetails::class);
       $validation = Validator::make($request->all(),[
            'file' => 'required|file|mimes:pdf|max:10000'
        ]);
        if($validation->fails()){
            $errors = $validation->errors();
            return response()->json($errors, 400);
        }
       $vc = Vaccination::find($id);
     //  $dp = DMProfile::where('dm_id',$dm->id)->first();
       // dd($dp);
       $vc->dose_2_complete = true;
       $vc->save();
       // Log::info("req".$request['dose_1_complete']);
       $file = $request['file']->getClientOriginalName();
       $extension = $request['file']->getClientOriginalExtension(); 
       // Log::info("ell".$extension);
       // Log::info("file".$file);
       $filename = pathinfo($file, PATHINFO_FILENAME);
       $dttime = Carbon::now()->format('YmdHis');
       $originalName = $filename.$dttime.'.'.$extension; 
       $cover = $request->file('file');
       Storage::disk('public')->put($originalName,File::get($cover));
       $request['dose_2_certificate_name'] = $originalName;
       $request['dose_2_certificate_path'] = Storage::disk('public')->url($originalName);
       $request['certificate_dose_2_upload_date'] = Carbon::now();
       $vc->update($request->only(['dose_2_certificate_name','dose_2_certificate_path','certificate_dose_2_upload_date']));
       return response()->json($vc,201); 
    }
    public function vaccinationStats()
    {
        $data = array();
        $d1vaccine = Vaccination::where('dose_1_complete',true)->get();
        $fvaccinated = Vaccination::where('dose_1_complete',true)->where('dose_2_complete',true)->get();
        $vaccinereg = Vaccination::all();
        $data['dose_1_vaccinated'] = count($d1vaccine);
        $data['fully_vaccinated'] = count($fvaccinated);
        $data['vaccine_reg'] = count($vaccinereg);
        return response()->json($data,200);

    }
    public function vaccinationStatsDmWise(Request $request,$dm_id)
    {
        $data = array();
        $d1vaccine = Vaccination::where('added_by',$dm_id)->where('dose_1_complete',true)->get();
        $fvaccinated = Vaccination::where('added_by',$dm_id)->where('dose_1_complete',true)->where('dose_2_complete',true)->get();
        $vaccinereg = Vaccination::where('added_by',$dm_id)->get();
        $data['dose_1_vaccinated'] = count($d1vaccine);
        $data['fully_vaccinated'] = count($fvaccinated);
        $data['vaccine_reg'] = count($vaccinereg);
        return response()->json($data,200);

    }
    public function getVaccineName()
    {
        $res = collect();
        $res->push('Covishield');
        $res->push('Covaxin');
        $res->push('Sputnik');
        return response()->json($res,200);
    }
    public function statusChange(Request $request,$id)
    {
        $validation = Validator::make($request->all(),[
            'dose_1_complete' => 'required|boolean',
            'dose_2_complete' => 'required|boolean'
        ]);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,400);
        }
        $vc = Vaccination::find($id);
        if($vc) {
            $vc->dose_1_complete = $request['dose_1_complete'];
            $vc->dose_2_complete = $request['dose_2_complete'];
            $vc->save();
            return response()->json($vc,200); 
           }
        return response()->json(['error'=>'Not Found'],404);   
    }
    public function getVaccinationByDm(Request $request,$dm_id){
         $vaccinations = Vaccination::where('added_by',$dm_id)->get();
         return response()->json($vaccinations->load('addedBy','person.personPersonalDetails','geography'),200);
    }

    public function getpersonsByVaccineDose(Request $request){
        
         $checktoken = $request->header('checktoken');
      //  dd($checktoken);
        if($checktoken != '0fQgVCL1cM2mGytOSovz') {
           return response()->json('Token Invalid',402);
        }
        
        $vaccinations = Vaccination::all()->groupBy('vaccine_name');
        $res = collect();
        foreach ($vaccinations as $key => $value) {
            $d1_count = 0;
            $d2_count = 0;
            $t_count = 0;
            foreach ($value as $v) {
               if($v->dose_1_complete) {
                $d1_count += 1; 
               }
               if($v->dose_2_complete) {
                $d2_count += 1; 

               } 
               if($v->vaccine_name == $key) {
                $t_count +=1;
               }
            }


            $temp[$key]['dose_2_complete'] = $d2_count;
            $temp[$key]['dose_1_complete'] = $d1_count;
            $temp[$key]['total_count'] = $t_count;
            $res->push($temp);
            $temp = null;
        }
        return response()->json($res,200);
    }

    public function getVaccinePeopleByDMFlage(Request $request, $flag){
         $checktoken = $request->header('checktoken');
      //  dd($checktoken);
        if($checktoken != '0fQgVCL1cM2mGytOSovz') {
           return response()->json('Token Invalid',402);
        }
        $dm = '';
        if($flag == 'CSP'){
            $dm = DrishteeMitra::whereHas('vaccinations',function($query){
            
            })->where('is_csp',1)->get();
        
        }else if($flag == 'VAANI'){
            $dm = DrishteeMitra::whereHas('vaccinations',function($query){
            
            })->where('is_vaani',1)->get();
        }else{
            $dm = DrishteeMitra::whereHas('vaccinations',function($query){
            
            })->where('is_vaani',0)->where('is_csp' , 0)->get();
        }

        return response()->json($dm->load('vaccinations.person'),200);
    }
    public function getVaccinationList(Request $request)
    {
      // dd("he0");
        // $vaccinations = null;
        // $vaccination = Vaccination::whereYear('certificate_dose_1_upload_date',$request['year'])->orWhereYear('certificate_dose_2_upload_date',$request['year'])->whereMonth('certificate_dose_1_upload_date',$request['month'])->orWhereMonth('certificate_dose_2_upload_date',$request['month']);
       // dd($request['all']);
        if($request['all'] == 'false') {
        $s = State::find($request['state_id']);
        $request['state'] = $s->name;
         // dd('hee');
         // $vaccination= Vaccination::whereYear('certificate_dose_1_upload_date',$request['year'])->orWhereYear('certificate_dose_2_upload_date',$request['year']);
         // $vaccination=$vaccination->whereMonth('certificate_dose_1_upload_date',$request['month'])->orWhereMonth('certificate_dose_2_upload_date',$request['month']);
         if($request['district'] == 'All') {
          $vaccination = Vaccination::whereHas('geography',function($query) use ($request) {
           $query->where('state',$request['state']);
        });   
         }
         else {
            $vaccination = Vaccination::whereHas('geography',function($query) use ($request) {
           $query->where('state',$request['state']);
          $query->where('district',$request['district']);
        });
         }
         
     //    dd($vaccination->toSql());
        $vaccinations= $vaccination->get(); 
        // return response()->json($vaccinations->load('geography'),200);
        }
        else {
            $vaccinationd = Vaccination::all();
            $vaccinations= $vaccinationd;
        }
        
        $vaccinations = $vaccinations;
        $res = collect();
        $i =1;
        foreach ($vaccinations as $vac) {
            if($vac->gender == 'female') {
               $d1_year = isset($vac->certificate_dose_1_upload_date) ? Carbon::parse($vac->certificate_dose_1_upload_date)->year:null;
            $d2_year = isset($vac->certificate_dose_2_upload_date) ? Carbon::parse($vac->certificate_dose_2_upload_date)->year:null;
            $d1_month = isset($vac->certificate_dose_1_upload_date) ? Carbon::parse($vac->certificate_dose_1_upload_date)->month:null;
            $d2_month = isset($vac->certificate_dose_2_upload_date)? Carbon::parse($vac->certificate_dose_2_upload_date)->month:null;

            if(((isset($d1_year) && $d1_year == $request['year']) || (isset($d2_year) && $d2_year ==$request['year'])) && ((isset($d1_month) && $d1_month == $request['month']) || (isset($d2_month) && $d2_month == $request['month']))) {
               $temp['Sl No.'] =$i++;
            $temp['State'] = $vac->geography->state ? $vac->geography->state:'';
            $temp['District'] = $vac->geography->district ? $vac->geography->district:'';
            $temp['Block'] = $vac->geography->block ? $vac->geography->block:'';
            $temp['Vatika'] =  $vac->geography->name;
            $name = $vac->person->first_name;
            if($vac->person->middle_name) {
              $name = $name.' '.$vac->person->middle_name.' '.$vac->person->last_name;
            }
            else {
               $name = $name.' '.$vac->person->last_name; 
            }
            $temp['Name'] = $name;
            // $temp['d1_month']= $d1_month;
            // $temp['d2_month'] = $d2_month;
            // $temp['d1_year'] = $d1_year;
            // $temp['d2_year'] = $d2_year;
            $temp['Gender'] = $vac->gender ? $vac->gender :'';
            $temp['Type'] = ($vac->addedBy && $vac->addedBy->type) ? $vac->addedBy->type :'' ;
            $temp['Mobile'] = $vac->person->mobile;
            $aname = null;
            if( $vac->addedBy && $vac->addedBy->middle_name) {
                $aname = $vac->addedBy->first_name.' '.$vac->addedBy->middle_name.' '.$vac->addedBy->last_name;
            }
            else {
                $aname = ($vac->addedBy && $vac->addedBy->first_name) ?$vac->addedBy->first_name:''.($vac->addedBy && $vac->addedBy->last_name) ? $vac->addedBy->last_name:'';

            }
            $temp["Added by Name"] =  $aname;
            $temp['CSP/CEP/Vaani Code'] = ($vac->addedBy && $vac->addedBy->code) ?$vac->addedBy->code:'' ;

            $temp['Year'] = $request['year'];
            $temp['Month'] = $request['month'];
            $temp['Beneficiary Reference ID(Dose 1)'] = $vac->dose_1_complete ? $vac->reg_id : '';
            $temp['Beneficiary Reference ID(Dose 2)'] = $vac->dose_2_complete ? $vac->reg_id : '';
            $temp['Bank'] = $vac->person->bankAccount ? $vac->person->bankAccount->bank_name:'';
            $temp['Payee Name'] = $vac->person->bankAccount ? $vac->person->bankAccount->payee_name:'';
            $temp['A/C No'] = $vac->person->bankAccount ? $vac->person->bankAccount->account_number:'';
            $temp['IFSC'] = $vac->person->bankAccount? $vac->person->bankAccount->ifsc_code:'';
            $res->push($temp);
            $temp = null; 
            } 
            }
             
            

        }
        $file = Carbon::now()->format('YmdHis').'vaccinationReport.xlsx';
    $filepath = (new FastExcel($res))->export(storage_path().'/'.$file);
    $url = "api/comissionreportmonthwise/".$file;
    return response()->json(url($url),200);
       // return response()->json($res,200);
       // dd($vaccination);
    }
    public function dateAssign()
    {
        $vaccinations = Vaccination::all();
        foreach ($vaccinations as $vac) {
            $vac->certificate_dose_1_upload_date = null;
            $vac->certificate_dose_2_upload_date = null;
            $vac->save();
            if(isset($vac['dose_1_certificate_name'])) {
            $vac->certificate_dose_1_upload_date = "2021-08-30 04:38:33";

            }
            if(isset($vac['dose_2_certificate_name'])) {
            $vac->certificate_dose_1_upload_date = "2021-08-30 04:38:33";
                             
            }
           // $vac->certificate_dose_2_upload_date = Carbon::now();
            $vac->save();
        }
        return response()->json($vaccinations,200);
    }
    
    public function vaccinationStatsExternal(Request $request){
        $checktoken = $request->header('checktoken');
      //  dd($checktoken);
        if($checktoken != '0fQgVCL1cM2mGytOSovz') {
           return response()->json('Token Invalid',402);
        }
        $vaccinations = Vaccination::all();
        $res = collect();
        foreach ($vaccinations as $vaccination) {
            $temp['state']= $vaccination->geography->state;
            $temp['district'] = $vaccination->geography->district;
            $temp['is_dose_1'] = $vaccination->dose_1_complete;
            $temp['is_dose_2'] = $vaccination->dose_2_complete;
            $temp['person_id'] = $vaccination->person_id;
            $res->push($temp);
            $temp = null;

        }
       $lres = $res->groupBy('state');
       $fres = collect();
       foreach ($lres as $key=>$value) {
           $dgroup = $value->groupBy('district');
           foreach ($dgroup as $k=>$v) {
             $tempp['state'] = $key;
             $tempp['district'] = $k;
             $tempp['count'] = count($v);
             $d1_count = 0;
             $d2_count = 0; 
             foreach ($v as $vac) {
                 if($vac['is_dose_1'] == true){
                   $d1_count +=1;
                 }
                 if($vac['is_dose_2'] == true) {
                    $d2_count +=1;
                 }
             }
             $tempp['d1_count'] = $d1_count;
             $tempp['d2_count'] = $d2_count;
             $fres->push($tempp);
             $tempp = null;
           }
       }
     return response()->json($fres,200);
    }
}
