<?php

namespace App\Http\Controllers;

use App\Person;
use Illuminate\Http\Request;
use App\Repositories\Repository\PersonRepository;
use App\Http\Requests\PersonSaveRequest;
use App\Http\Requests\PersonLocationSaveRequest;
use App\Http\Requests\PersonPersonalSaveRequest;
use App\Http\Requests\PersonBankAccountDetailsSaveRequest;
use Validator;
use App\Http\Requests\PersonKycSaveRequest;
use App\Http\Requests\PersonEducationRequest;
use App\Http\Requests\PersonInfrastructureRequest;
use App\Http\Requests\PersonIncomeRequest;
use App\Http\Requests\PersonSkillRequest;
use App\Http\Requests\PersonLiveHoodEngagementRequest;
use App\Http\Requests\PersonTrainingRequest;
use App\Http\Requests\PersonWorkExperienceRequest;
use App\PersonSkill;
use App\PersonWorkExperience;
use App\PersonTraining;
use Carbon\Carbon;
use App\PersonKycDetail;
use Storage;
use File;
use App\PersonPersonalDetail;
use App\Ledger;
use App\LedgerTransaction;
use Rap2hpoutre\FastExcel\FastExcel;
use Rap2hpoutre\FastExcel\SheetCollection;
use App\Geography;
use App\PersonBankAccountDetail;
use App\PersonLocation;
use App\PersonEducation;
use App\PersonInfrastructureDetail;
use App\PersonIncome;
use App\PersonLiveHoodEngagement;
use Log;
use App\PersonProduct;
use App\PersonService;
use App\Barter;
use App\DrishteeMitra;
use App\Jobs\SendEmail;
use Queue;
class PersonController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private $repository;
    public function __construct(PersonRepository $repository)
    {
        $this->repository = $repository;
    }

    public function exportPersonWithLp(){
        $persons =  Person::whereHas('ledger', function ($query) {
            $query->where('balance', '!=', 0);
        })->get();
        // $persons = Person::find(3);
        // return response()->json($persons->state,200);
        // dd($persons->state->name);
        $data = [];
        foreach ($persons as $person) {
            // Log::info("ledger".$person->id);
            array_push($data,[
                "Person Name"=> $person->first_name.' '.$person->last_name,
                "Mobile"=> $person->mobile,
                "Email"=>$person && $person->email ? $person->email:'',                
                "State"=> $person && $person->state  ? $person->state:'',
                "Geography"=> $person && $person->geographies ?$person->geographies->name:'',
                "Mitra Name"=> (($person->dm) ? ($person->dm->first_name.' '.$person->dm->last_name): ""),
                "Lp" => $person->ledger->balance
                 ]);
        }

        $file = Carbon::now()->format('YmdHis').'personWithLp.xlsx';

        $filepath = (new FastExcel($data))->export(storage_path().'/'.$file);
        $url = "api/person/export/".$file;
        return response()->json(url($url),200);
    }

    public function exportPersonWithNoLp(){
        $persons =  Person::whereHas('ledger', function ($query) {
            $query->where('balance', '==', 0);
        })->get();

        $data = [];
        foreach ($persons as $person) {
            array_push($data,[
                "Person Name"=> $person->first_name.' '.$person->last_name,
                "Mobile"=> $person->mobile,
                "Email"=>$person->email,                
                "State"=> $person->state ? $person->state:'',
                "Geography"=> $person->geographies ? $person->geographies->name:'',
                "Mitra Name"=> (($person->dm) ? ($person->dm->first_name.' '.$person->dm->last_name): ""),
                "Lp" => $person->ledger->balance
                 ]);
        }

        $file = Carbon::now()->format('YmdHis').'personWithNoLp.xlsx';

        $filepath = (new FastExcel($data))->export(storage_path().'/'.$file);
        $url = "api/person/export/".$file;
        return response()->json(url($url),200);
    }

    public function exportPerson($person_id){
        $person = Person::find($person_id);
        $file = Carbon::now()->format('YmdHis').'personDetails.xlsx';
        
        $sheets = new SheetCollection([
            'Person' => Person::where('id',$person_id)->get(),
            'PersonProfile' => PersonPersonalDetail::where('person_id',$person_id)->get(),
            'Bank Account'=> PersonBankAccountDetail::where('person_id',$person_id)->get(),
            'Location'=> PersonLocation::where('person_id',$person_id)->get(),
            'Kyc Detail'=> PersonKycDetail::where('person_id',$person_id)->get(),
            'Education'=> PersonEducation::where('person_id',$person_id)->get(),
            'Infrastructure'=> PersonInfrastructureDetail::where('person_id',$person_id)->get(),
            'Income'=> PersonIncome::where('person_id',$person_id)->get(),
            'Skills'=> PersonSkill::where('person_id',$person_id)->get(),
            'Work Experiences'=>PersonWorkExperience::where('person_id',$person_id)->get(),
            'Live Hood Engagement'=> PersonLiveHoodEngagement::where('person_id',$person_id)->get(),
            'Trainings'=> PersonTraining::where('person_id',$person_id)->get(),
        ]);
        $filepath= (new FastExcel($sheets))->export(storage_path().'/'.$file);
        $url = "api/person/export/".$file;
        return response()->json(url($url),200);
    }
    public function downloadExportPerson($filename){
        $file = basename($filename);
        $filepath = storage_path().'/'.$file;
        return response()->download($filepath, $file, [
            'Content-Length: '. filesize($filepath)
        ]);
    }
    public function filteredPersons(Request $request){
        $persons =  Person::filterPersons($request->all());
        if($request['export'] == true) {
            $res = Person::transformPersonData($persons);
            // return respnse()->json($res,200);
            $file = Carbon::now()->format('YmdHis').'filterPersonAllDetails.xlsx';
        
        // $sheets = new SheetCollection([
        //     'Person' => Person::where('id',$person_id)->get(),
        //     'PersonProfile' => PersonPersonalDetail::where('person_id',$person_id)->get(),
        //     'Bank Account'=> PersonBankAccountDetail::where('person_id',$person_id)->get(),
        //     'Location'=> PersonLocation::where('person_id',$person_id)->get(),
        //     'Kyc Detail'=> PersonKycDetail::where('person_id',$person_id)->get(),
        //     'Education'=> PersonEducation::where('person_id',$person_id)->get(),
        //     'Infrastructure'=> PersonInfrastructureDetail::where('person_id',$person_id)->get(),
        //     'Income'=> PersonIncome::where('person_id',$person_id)->get(),
        //     'Skills'=> PersonSkill::where('person_id',$person_id)->get(),
        //     'Work Experiences'=>PersonWorkExperience::where('person_id',$person_id)->get(),
        //     'Live Hood Engagement'=> PersonLiveHoodEngagement::where('person_id',$person_id)->get(),
        //     'Trainings'=> PersonTraining::where('person_id',$person_id)->get(),
        // ]);
        $filepath= (new FastExcel($res))->export(storage_path().'/'.$file);
        $url = "api/person/export/".$file;
       return response()->json(url($url),200);
        }
        return response()->json($persons,200);
    }
    public function index(){
         
        return response()->json($this->repository->all(),200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(){
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){
        $validation = Validator::make($request->all(),[
            'first_name'           => 'required',
            'middle_name'          => 'sometimes',
            'last_name'            => 'required', 
             'email'                => 'sometimes|nullable|email',
            'mobile'               => 'required|digits:10|unique:people',
            'geography_id'         => 'required|exists:geographies,id',
            'geography_type'       => 'required' ,
            'ledger_id'            => 'sometimes',
            'status'               => 'sometimes',
            'otp'                  => 'sometimes',
            'dm_id'                => 'sometimes|required|exists:drishtree_mitras,id',
            'add_by_user_id'       => 'sometimes|required|exists:users,id',
            'added_on'             => 'sometimes',
            'is_profile_complete'  => 'sometimes',
            'account_number'   => 'required',
            'bank_name'        => 'required',
            'ifsc_code'        => 'required',
            'payee_name'       => 'required',
            'branch_name'      => 'sometimes',
            'branch_address'  => 'sometimes',
            'vaccinated'      => 'sometimes',
            'dose_1'          => 'sometimes',
            'dose_2'          => 'sometimes',
            'precaution_dose' => 'sometimes'
        ]);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,400);
        }
        $request['added_on'] = Carbon::now();
        // Log::info("person".$request);
        // dd($added_on); 
        $geography = Geography::find($request['geography_id']);
        $request['state'] = $geography->state ? $geography->state :''; 
        $request['district'] = $geography->district ? $geography->district :'';
        $request['block'] = $geography->block ? $geography->block :'';
        $person = $this->repository->create($request->all());
        $template_id = 1207161761324100980;
        sendSMS('Welcome to Miri Market Barter. Your producer account has been created successfully.',$person->mobile,$template_id);

        $person->createPersonTransaction('Success',0);
        $person_ledger = Ledger::where('ledger_id',$person->id)->where('ledger_type','App\Person')->first();
        $person->ledger_id = $person_ledger->id;
        $person->save();
        $temp['person_id'] = $person->id;
        $bank['person_id'] = $person->id;
        $bank['account_number'] = $request['account_number'];
        $bank['bank_name'] = $request['bank_name'];
        $bank['ifsc_code'] = $request['ifsc_code'];
        $bank['payee_name'] = $request['payee_name'];
        $bank['branch_address'] = $request['branch_address'];
        $bank['branch_name'] = $request['branch_name'];
        $personPersonalDetails = $person->personPersonalDetails()->create($temp);
        $persobank = $person->bankAccount()->create($bank);
        return response()->json($person->load('personPersonalDetails','bankAccount'),200);
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Person  $person
     * @return \Illuminate\Http\Response
     */
    public function show($id){

        // dd($person->id);
        $person = Person::find($id);
        if($person) {
            // dd("hello");
            return response()->json($this->repository->findById($id)->load('dm.dmProfile','ledgers','barters.barterHaveServices.personService.service','barters.barterHaveProducts.personProduct.product.units','barters.barterHaveLp','barters.barterNeedProducts.product.units','barters.barterNeedServices.service','barters.barterNeedLp','personServices.service','geographies','bankAccount','personPersonalDetails','personLocation','personKycDetail','personEducation','personInfrastructure','personIncome','personSkills','personWorkExperiences','personLiveHoodEngagement','personTrainings','personProducts.product'),200);
        }
        return response()->json(['error'=>'Not Found'],404);
        
        // return response()->json($this->repository->findById($person->id)->load('geographies','bankAccount','personPersonalDetails','personLocation','personKycDetail','personEducation','personInfrastructure','personIncome','personSkills','personWorkExperiences','personLiveHoodEngagement','personTrainings','ledgers','personProducts'),200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Person  $person
     * @return \Illuminate\Http\Response
     */
    public function edit(Person $person)
    {
        

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Person  $person
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Person $person){
        $validation = Validator::make($request->all(),[
            'first_name'           => 'required',
            'middle_name'          => 'sometimes',
            'last_name'            => 'required', 
            'email'                => 'sometimes|nullable|email|unique:people,email,'.$person->id,
            'mobile'               => 'required|digits:10|unique:people,mobile,'.$person->id,
            'ledger_id'            => 'sometimes',
            'status'               => 'sometimes',
            'dm_id'                => 'sometimes|required|exists:drishtree_mitras,id',
            'add_by_user_id'       => 'sometimes|required|exists:users,id',
            'added_on'             => 'sometimes',
            'is_profile_complete'  => 'sometimes',
            'account_number'   => 'sometimes|numeric',
            'bank_name'        => 'sometimes',
            'ifsc_code'        => 'sometimes',
            'payee_name'       => 'sometimes',
            'branch_name'      => 'sometimes',
            'branch_address'  => 'sometimes',
            'vaccinated'      => 'sometimes',
            'dose_1'          => 'sometimes',
            'dose_2'          => 'sometimes',
            'precaution_dose' => 'sometimes'
        ]);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,400);
        }
        // return response()->json($person,200);
        $person = Person::find($person->id);
        if($person) {
        $geography = Geography::find($request['geography_id']);
        $request['state'] = $geography->state ? $geography->state :''; 
        $request['district'] = $geography->district ? $geography->district :'';
        $request['block'] = $geography->block ? $geography->block :'';
           //geography_id , geography_type have to add
            $person->update($request->only('first_name','middle_name','last_name','email','mobile','ledger_id','otp','status','dm_id','add_by_user_id','otp','added_on','is_profile_complete','available_lp','state_id','state','district','block','vaccinated','dose_1','dose_2','precaution_dose'));
            $personbank = $person->bankAccount()->update($request->only('account_number','bank_name','ifsc_code','payee_name','branch_name','branch_address'));
            if(!$personbank){
                $PBAD = $person->bankAccount()->create($request->only('account_number','bank_name','ifsc_code','payee_name'));
            }
        }
        return response()->json($person->load('bankAccount'),201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Person  $person
     * @return \Illuminate\Http\Response
     */
    public function destroy(Person $person)
    {
        //
    }

    /**
    * @param  \Illuminate\Http\PersonLocation $request
    * @param  \App\person $id
    * @return \App\personLocation $pl
    * do store and update personLocation
    */
    public function personLocationSave(PersonLocationSaveRequest $request,$id){
        $validation = Validator::make($request->all(),[
            'state'          => 'sometimes',
            'city'           => 'sometimes',
            'block'          => 'sometimes', 
            'village'        => 'sometimes',
            'latitude'       => 'sometimes',
            'longitude'      => 'sometimes',
            'pincode'        => 'sometimes',
            'area_type'      => 'sometimes',
            
        ]);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,400);
        }
        $person = Person::find($id);
        if($person) {
            if($person->personLocation){
                $pl = $person->personLocation()->update($request->except('id','created_at'));
            }else {
                $pl = $person->personLocation()->create($request->all());
            }
            return response()->json($pl,200);
        }
        return response()->json(['error'=>'Person Not Found'],404);
    }

    /**
    * @param  \Illuminate\Http\PersonPersonalDetail $request
    * @param  \App\person $id
    * @return \App\PersonPersonalDetail $pl
    * do store and update PersonPersonalDetail
    */
    public function personPersonalSave(PersonPersonalSaveRequest $request,$id){
        $validation = Validator::make($request->all(),[
            'dob'               => 'sometimes',
            'marital_status'    => 'sometimes',
            'gender'            => 'sometimes',
            'disability'        => 'sometimes',
            'religion'          => 'sometimes',
            'caste'             => 'sometimes',
            'language'          => 'sometimes',
            'photo_name'        => 'sometimes',
            'photo_path'        => 'sometimes',
        ]);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,400);
        }
        $person = Person::find($id);
        if($person) {
            if($person->personPersonalDetails) {
             //$pp = PersonPersonalDetail::find($person->personPersonalDetails->id);   
            $person->personPersonalDetails()->update($request->except('id'));
            return response()->json($person,200);
            
            }
            else
            {
            $pl = $person->personPersonalDetails()->create($request->all());

            }
            return response()->json($pl,200);
        }
        return response()->json(['error'=>'Person Not Found'],404); 
    }

    /**
    * @param  \Illuminate\Http\PersonBankAccountDetail $request
    * @param  \App\person $id
    * @return \App\PersonBankAccountDetail $pl
    * do store and update PersonBankAccountDetail
    */
    public function personBankAccountDetailSave(PersonBankAccountDetailsSaveRequest $request,$id){
        $validation = Validator::make($request->all(),[
            'account_number'  => 'required|numeric', //numeric
            'bank_name'       => 'required',
            'ifsc_code'       => 'required',
            'payee_name'      => 'required',
            'branch_name'      => 'required',
            'branch_address'  => 'required'
        ]);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,400);
        }
        
        $person = Person::find($id);
        if($person) {
            if($person->bankAccount)
            {

            $pl = $person->bankAccount()->update($request->except('id'));
            
            }
            else {
            $pl = $person->bankAccount()->create($request->all());
                            
            }
            return response()->json($pl,200);
        }
        return response()->json(['error'=>'Person Not Found'],404);   
    }


    /**
    * @param  \Illuminate\Http\PersonKycDetail $request
    * @param  \App\person $id
    * @return \App\PersonKycDetail $pl
    * do store and update PersonKycDetail
    */
    public function personKycDetailsSave(PersonKycSaveRequest $request,$id){
        $validation = Validator::make($request->all(),[
            'adhar_card_no'         => 'sometimes|nullable|digits:12',
            'is_kyc_done'           => 'sometimes',
            'adhar_card_photo_name' => 'sometimes',
            'adhar_card_photo_path' => 'sometimes',
            'pancard_no'            => 'sometimes|nullable|min:10|max:10',
            'pancard_photo_name'    => 'sometimes',
            'pancard_photo_path'    => 'sometimes',
            'dl_no'                 => 'sometimes',
            'dl_photo_name'         => 'sometimes',
            'dl_photo_path'         => 'sometimes',
            'passport_no'           => 'sometimes',
            'passport_photo_name'   => 'sometimes',
            'passport_photo_path'   => 'sometimes',
            'voter_id_no'           => 'sometimes',
            'voter_id_photo_name'   => 'sometimes',
            'voter_id_photo_path'   => 'sometimes',
        ]);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,400);
        }

        $person = Person::find($id);
        if($person) {
            if($person->personKycDetail)
            {
              $pkyc = PersonKycDetail::find($person->personKycDetail->id);
              $pkyc->update($request->all());   
              return response()->json($pkyc);
            // $pl = $person->personKycDetail()->update($request->except('id'));
             
             }
            else {
            $pl = $person->personKycDetail()->create($request->all());
            return response()->json($pl,200);
                            
            }
        }
        return response()->json(['error'=>'Person Not Found'],404);
    }

    /**
    * @param  \Illuminate\Http\PersonEducation $request
    * @param  \App\person $id
    * @return \App\PersonEducation $pl
    * do store and update PersonEducation
    */
    public function personEducationSave(PersonEducationRequest $request,$id){
        
        $person = Person::find($id);
        if($person) {
            if($person->personEducation)
            {
            $pl = $person->personEducation()->update($request->except('id'));
            }
            else {
            $pl = $person->personEducation()->create($request->all());
                            
            }
            return response()->json($pl,200);
        }
        return response()->json(['error'=>'Person Not Found'],404);   
    }
    
    // PersonInfrastructureRequest

    /**
    * @param  \Illuminate\Http\PersonInfrastructureDetail $request
    * @param  \App\person $id
    * @return \App\PersonInfrastructureDetail $pl
    * do store and update PersonInfrastructureDetail
    */
    public function personInfrastructureSave(PersonInfrastructureRequest $request,$id){
     $person = Person::find($id);
        if($person) {
            if($person->personInfrastructure)
            {
            $pl = $person->personInfrastructure()->update($request->except('id'));
            }
            else {
            $pl = $person->personInfrastructure()->create($request->all());
                            
            }
            return response()->json($pl,200);
        }
        return response()->json(['error'=>'Person Not Found'],404);   
    }


    /**
    * @param  \Illuminate\Http\PersonIncome $request
    * @param  \App\person $id
    * @return \App\PersonIncome $pl
    * do store and update PersonIncome
    */
    public function personIncomeSave(PersonIncomeRequest $request,$id){
     $person = Person::find($id);
        if($person) {
            if($person->personIncome)
            {
            $pl = $person->personIncome()->update($request->except('id'));
            }
            else {
            $pl = $person->personIncome()->create($request->all());
                            
            }
            return response()->json($pl,200);
        }
        return response()->json(['error'=>'Person Not Found'],404);   
    }

    /**
    * @param  \Illuminate\Http\PersonSkill $request
    * @param  \App\person $id
    * @return \App\PersonSkill $pl
    * do store PersonSkill
    */
    public function personSkillSave(PersonSkillRequest $request,$id){
     $person = Person::find($id);
        if($person) {
            // if($person->personSkills)
            // {
            // $pl = $person->personSkills()->update($request->except('id'));
            // }
            // else {
            $pl = $person->personSkills()->create($request->all());
                            
            // }
            return response()->json($pl,200);
        }
        return response()->json(['error'=>'Person Not Found'],404);   
    
    }

    /**
    * @param  \Illuminate\Http\PersonSkill $request
    * @param  \App\person $id
    * @param  \App\PersonSkill $skill_id
    * @return \App\PersonSkill $pwe
    * do update PersonSkill
    */
    public function personSkillUpdate(PersonSkillRequest $request,$person_id,$skill_id){
     $person = Person::find($person_id);
        if($person) {
            $pwe = PersonSkill::find($skill_id);
            if($pwe) {
            $pwe->update($request->all());
            return response()->json($pwe,200);
            }
            return respnse()->json(['error'=>'PS Not Found']);
        }
        return response()->json(['error'=>'Person Not Found'],404);   
    }


    /**
    * @param  \Illuminate\Http\PersonWorkExperience $request
    * @param  \App\person $id
    * @return \App\PersonWorkExperience $pl
    * do store PersonWorkExperience
    */
    public function personWorkingExperienceSave(PersonWorkExperienceRequest $request,$id){
     $person = Person::find($id);
        if($person) {
            $pl = $person->personWorkExperiences()->create($request->all());
            return response()->json($pl,200);
        }
        return response()->json(['error'=>'Person Not Found'],404);   
    }

    /**
    * @param  \Illuminate\Http\PersonWorkExperience $request
    * @param  \App\person $id
    * @param  \App\PersonWorkExperience $experience_id
    * @return \App\PersonWorkExperience $pwe
    * do update PersonWorkExperience
    */
    public function personWorkingExperienceUpdate(PersonWorkExperienceRequest $request,$person_id,$experience_id){
     $person = Person::find($person_id);
        if($person) {
            $pwe = PersonWorkExperience::find($experience_id);
            if($pwe) {
            $pwe->update($request->all());
            return response()->json($pwe,200);
            }
            return respnse()->json(['error'=>'PWE Not Found']);
        }
        return response()->json(['error'=>'Person Not Found'],404);   
    }

    /**
    * @param  \Illuminate\Http\PersonTraining $request
    * @param  \App\person $id
    * @return \App\PersonTraining $pl
    * do store PersonTraining
    */
    public function personTrainingSave(PersonTrainingRequest $request,$id){
     $person = Person::find($id);
        if($person) {
            // if($person->personTrainings)api
            // {
            // $pl = $person->personTrainings()->update($request->except('id'));
            // }
            // else {
            $pl = $person->personTrainings()->create($request->all());
                            
            // }
            return response()->json($pl,200);
        }
        return response()->json(['error'=>'Person Not Found'],404);   
    }

    /**
    * @param  \Illuminate\Http\PersonTraining $request
    * @param  \App\person $person_id
    * @param  \App\PersonTraining $training_id
    * @return \App\PersonTraining $pwe
    * do update PersonTraining
    */
    public function personTrainingUpdate(PersonTrainingRequest $request,$person_id,$training_id){
     $person = Person::find($person_id);
        if($person) {
            $pwe = PersonTraining::find($training_id);
            if($pwe) {
            $pwe->update($request->all());
            return response()->json($pwe,200);
            }
            return respnse()->json(['error'=>'PT Not Found']);
        }
        return response()->json(['error'=>'Person Not Found'],404);   
    }

    /**
    * @param  \Illuminate\Http\PersonLiveHoodEngagement $request
    * @param  \App\person $id
    * @return \App\PersonLiveHoodEngagement $pl
    * do store and update PersonLiveHoodEngagement
    */
    public function personLiveHoodEngagementSave(PersonLiveHoodEngagementRequest $request,$id){
     $person = Person::find($id);
        if($person) {
            if($person->personLiveHoodEngagement)
            {
            $pl = $person->personLiveHoodEngagement()->update($request->except('id'));
            }
            else {
            $pl = $person->personLiveHoodEngagement()->create($request->all());
                            
            }
            return response()->json($pl,200);
        }
        return response()->json(['error'=>'Person Not Found'],404);   
    }

    /**
    * @param  \App\person $person_id
    * @param  \App\PersonTraining $training_id
    * @return \App\PersonTraining $pwe
    * do delete PersonTraining
    */
    public function deleteTraining($person_id,$training_id){
        $person = Person::find($person_id);
        if($person) {
            $pwe = PersonTraining::find($training_id);
            if($pwe) {
            $pwe->destroy($pwe->id);
            return response()->json($pwe,200);
            }
            return respnse()->json(['error'=>'PT Not Found']);
        }
        return response()->json(['error'=>'Person Not Found'],404);
    }

    /**
    * @param  \App\person $person_id
    * @param  \App\PersonSkill $skill_id
    * @return \App\PersonSkill $pwe
    * do delete PersonSkill
    */
    public function deleteSkill($person_id,$skill_id){
     $person = Person::find($person_id);
        if($person) {
            $pwe = PersonSkill::find($skill_id);
            if($pwe) {
            $pwe->destroy($pwe->id);
            return response()->json($pwe,200);
            }
            return respnse()->json(['error'=>'PS Not Found']);
        }
        return response()->json(['error'=>'Person Not Found'],404);   
    }

    /**
    * @param  \App\person $person_id
    * @param  \App\PersonWorkExperience $experience_id
    * @return \App\PersonWorkExperience $pwe
    * do delete PersonWorkExperience
    */
    public function deleteWorkExperience($person_id,$experience_id){
     $person = Person::find($person_id);
        if($person) {
            $pwe = PersonWorkExperience::find($experience_id);
            if($pwe) {
            $pwe->destroy($pwe->id);
            return response()->json($pwe,200);
            }
            return respnse()->json(['error'=>'PWE Not Found']);
        }
        return response()->json(['error'=>'Person Not Found'],404);   
    }

    /**
    * @param  \Illuminate\Http\Request  $request
    * @param  \App\PersonKycDetail $id
    * @return \App\PersonKycDetail $pc
    * do upload person document pancard, aadharcard, dl, votercard, passport
    */
    public function uploadKycImage(Request $request,$id){
        $validation = Validator::make($request->all(),[
                'pan_file'      => 'sometimes',
                'aadhar_file'   => 'sometimes',
                'passport_file' => 'sometimes',
                'dl_file'       => 'sometimes',
                'voter_file'    => 'sometimes'
         ]);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,400);
        }
       $pc = PersonKycDetail::find($id);
       if($request->file('pan_file')) {
           $file = $request['pan_file']->getClientOriginalName();
           $extension = $request['pan_file']->getClientOriginalExtension(); 
           $filename = pathinfo($file, PATHINFO_FILENAME);
           $originalName = $filename.'.'.$extension; 
           $cover = $request->file('pan_file');
          // Storage::disk('public')->put($originalName,File::get($cover));
       $filePath = $request->file('pan_file')->storeAs('databackup/', $originalName, 'azure');
          
           $request['pancard_photo_name'] = $originalName;
          // $request['pancard_photo_path'] = Storage::disk('public')->url($originalName);
           $request['pancard_photo_path'] = 'https://drishteedatastore.blob.core.windows.net/drishtee/databackup/'.$originalName;
           $pc->update($request->only(['pancard_photo_path','pancard_photo_path']));
       }
       if($request->file('dl_file')) {
           $file = $request['dl_file']->getClientOriginalName();
           $extension = $request['dl_file']->getClientOriginalExtension(); 
           $filename = pathinfo($file, PATHINFO_FILENAME);
           $originalName = $filename.'.'.$extension; 
           $cover = $request->file('dl_file');
       $filePath = $request->file('dl_file')->storeAs('databackup/', $originalName, 'azure');
            
          // Storage::disk('public')->put($originalName,File::get($cover));
           $request['dl_photo_name'] = $originalName;
         //  $request['dl_photo_path'] = Storage::disk('public')->url($originalName);
             $request['dl_photo_path'] = 'https://drishteedatastore.blob.core.windows.net/drishtee/databackup/'.$originalName;
           $pc->update($request->only(['dl_photo_name','dl_photo_path']));
       }
       if($request->file('aadhar_file')) {
           $file = $request['aadhar_file']->getClientOriginalName();
           $extension = $request['aadhar_file']->getClientOriginalExtension(); 
           $filename = pathinfo($file, PATHINFO_FILENAME);
           $originalName = $filename.'.'.$extension; 
           $cover = $request->file('aadhar_file');
          // Storage::disk('public')->put($originalName,File::get($cover));
       $filePath = $request->file('aadhar_file')->storeAs('databackup/', $originalName, 'azure');
           
           $request['adhar_card_photo_name'] = $originalName;
         //  $request['adhar_card_photo_path'] = Storage::disk('public')->url($originalName);
           $request['adhar_card_photo_path'] = 'https://drishteedatastore.blob.core.windows.net/drishtee/databackup/'.$originalName;
           $pc->update($request->only(['adhar_card_photo_name','adhar_card_photo_path']));
       }
       if($request->file('passport_file')) {
           $file = $request['passport_file']->getClientOriginalName();
           $extension = $request['passport_file']->getClientOriginalExtension(); 
           $filename = pathinfo($file, PATHINFO_FILENAME);
           $originalName = $filename.'.'.$extension; 
           $cover = $request->file('passport_file');
           //Storage::disk('public')->put($originalName,File::get($cover));
           $filePath = $request->file('passport_file')->storeAs('databackup/', $originalName, 'azure');
           
           $request['passport_photo_name'] = $originalName;
           //$request['passport_photo_path'] = Storage::disk('public')->url($originalName);
           $request['passport_photo_path'] = 'https://drishteedatastore.blob.core.windows.net/drishtee/databackup/'.$originalName;
           $pc->update($request->only(['passport_photo_path','passport_photo_name']));
       }
       if($request->file('voter_file')) {
           $file = $request['voter_file']->getClientOriginalName();
           $extension = $request['voter_file']->getClientOriginalExtension(); 
           $filename = pathinfo($file, PATHINFO_FILENAME);
           $originalName = $filename.'.'.$extension; 
           $cover = $request->file('voter_file');
          // Storage::disk('public')->put($originalName,File::get($cover));
           $filePath = $request->file('voter_file')->storeAs('databackup/', $originalName, 'azure');
           
           $request['voter_id_photo_name'] = $originalName;
          // $request['voter_id_photo_path'] = Storage::disk('public')->url($originalName);
           $request['voter_id_photo_path'] = 'https://drishteedatastore.blob.core.windows.net/drishtee/databackup/'.$originalName;
           $pc->update($request->only(['voter_id_photo_name','voter_id_photo_path']));
       }
       return response()->json($pc,200);  
    }

    /**
    * @param  \Illuminate\Http\Request  $request
    * @param  \App\PersonPersonalDetail $id
    * @return \App\PersonPersonalDetail $ppd
    * do upload person profile image
    */
    public function uploadImage(Request $request,$id){
        // dd($request['file']);
       //$this->authorize('create',StaffBasicDetails::class);
       $validation = Validator::make($request->all(),[
            'file' => 'required|file|mimes:jpg,jpeg,png,bmp'
        ]);
        if($validation->fails()){
            $errors = $validation->errors();
            return response()->json($errors, 400);
        }
       $ppd = PersonPersonalDetail::find($id);
       // dd($dp);
       $file = $request['file']->getClientOriginalName();
       $extension = $request['file']->getClientOriginalExtension(); 
       $filename = pathinfo($file, PATHINFO_FILENAME);
       $originalName = $filename.'.'.$extension; 
       $cover = $request->file('file');
       $filePath = $request->file('file')->storeAs('databackup/', $originalName, 'azure');
       // dd($ppd); 
       // dd($filePath); 
       //Storage::disk('public')->put($originalName,File::get($cover));
       $request['photo_name'] = $originalName;
       $request['photo_path'] = 'https://drishteedatastore.blob.core.windows.net/drishtee/databackup/'.$originalName;
       $ppd->update($request->only(['photo_name','photo_path']));
       return response()->json($ppd,201); 
    }

    /**
    * @param  \App\DrishteeMitra $id
    * @return \App\PersonTraining $pl
    * do get all person related to DM $id with geographies','bankAccount','personPersonalDetails','personLocation','personKycDetail','personEducation','personInfrastructure','personIncome','personSkills','personWorkExperiences','personLiveHoodEngagement','personTrainings','ledgers
    */
    public function getDmPersons($id){
        $dm = DrishteeMitra::find($id);

        $persons = Person::where('geography_id',$dm->dmGeography->geography_id)->get();
        return response()->json($persons->load('geographies','bankAccount','personPersonalDetails','personLocation','personKycDetail','personEducation','personInfrastructure','personIncome','personSkills','personWorkExperiences','personLiveHoodEngagement','personTrainings','ledgers'),200);
    }

    /**
    * @param  \App\person $id
    * @return \App\Ledger $ledger
    * do get person lp
    */
    public function personLpGet($id){
        $person = Person::find($id);
        $ledger = Ledger::find($person->ledger_id);
        return response()->json($ledger,200);
    }


    /**
    * @param  \App\person $id
    * @return \App\LedgerTransaction $person
    * do get list of transaction related to person
    */
    public function getPersonLedgerTransaction($id){

        $startdate = Carbon::now()->subDays(30)->format('Y/m/d');
        $enddate = Carbon::tomorrow()->format('Y/m/d');

        $transaction = LedgerTransaction::where('person_id',$id)->where('created_at','>=',$startdate)->where('created_at','<=',$enddate)->get();
        return response()->json($transaction,200);
             
    }

    public function sendSMS(Request $request){

      //  $url = 'http://sms.indiatext.in/api/mt/SendSMS?user=drishtee&password=drishtee123$&senderid=MIRICR&channel=Trans&DCS=0&flashsms=0&number='.$request['number'].'&text='.rawurlencode($request['message']).'&route=1';


       $url = 'http://sms.messageindia.in/v2/sendSMS?username=drishtee&message='.rawurlencode($request['message']).'&sendername=MIRICR&smstype=TRANS&numbers='.$request['number'].'&apikey=f4e36e84-59bf-47d8-968c-c44287e7e5eb&peid=1201161527747662237&templateid='.$request['template_id'];

       // $url = 'http://sms.messageindia.in/v2/sendSMS?username=drishtee&message=Your%20Drishtee%20Mitra%20login%20OTP%20is%2012345678.&sendername=MIRICR&smstype=TRANS&numbers=7355123279&apikey=f4e36e84-59bf-47d8-968c-c44287e7e5eb&peid=1201161527747662237&templateid=1207161761276469241';
     // $url  = 'https://google.com';
        $crl = curl_init();

        curl_setopt($crl, CURLOPT_URL, $url);
       curl_setopt($crl, CURLOPT_FRESH_CONNECT, true);
       curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($crl);
        // Log::info('here'.$response);
        dd($response);  
        if(!$response){
          //die('Error');

          die('Error: "' . curl_error($response) . '" - Code: ' . curl_errno($response));
        }
        curl_close($crl);       
    }

    /**
    * @param  \App\person $person_id
    * @param  \Illuminate\Http\Request  $request
    * @return \App\Person $person
    * do person status change
    */
    public function statusChange(Request $request,$person_id){
        $validation = Validator::make($request->all(),[
            'status'               => 'required',
        ]);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,400);
        }
        
        $person = Person::find($person_id);
        $person->status = $request->status;
        $person->save();
        return response()->json($person,200);
    }

    /**
    * @param  \App\person $person_id
    * @param  \Illuminate\Http\Request  $request
    * @return \App\PersonTraining $pl
    * do filter ledger in between start, end date of related person
    */
    public function ledgerfilter(Request $request, $person_id){
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
        $person = Person::find($person_id);
        $ledger = Ledger::find($person->ledger_id);

        $transaction = LedgerTransaction::where('ledger_id',$ledger->id)->where('created_at','>=',$startdate)->where('created_at','<=',$enddate)->get();
        return response()->json($transaction,200);
    }

    public function drishteePersonDetails(Request $request){
        
        $checktoken = $request->header('checktoken');
        if($checktoken != '0fQgVCL1cM2mGytOSovz') {
           return response()->json('Token Invalid',402);
        }

        $count = false;
        if($request->count){
            $count = true;
        }

        $personsDetail = Person::orderBy('id','DESC')->get();
        if($count){
            return response()->json(count($personsDetail),200);
        }

        return response()->json($personsDetail->load('personPersonalDetails'),200);
    }

    public function drishteeLPDetails(){
        $persons = Person::all(['mobile','ledger_id']);
        return response()->json($persons->load('ledger'),200);
    }

    public function drishteeTransactionDetails(Request $request){
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

        $transaction = LedgerTransaction::where('created_at','>=',$startdate)->where('created_at','<=',$enddate)->get();
        // $transaction = LedgerTransaction::all();
        return response()->json($transaction,200);
    }

    public function deletePerson($id){
        $person = Person::find($id);
        if($person) {
          $geo = $person->geographies;
          $state = $person->state;
          $dm = $person->dm;
          $product = $person->personProducts;
          $service = $person->personServices;
          $barter = $person->barters;
          if(count($product) > 0 || count($service) > 0 || count($barter) > 0) {
                return response()->json(['error'=>'you can not delete this Person'],400);

          }
          else {
            $person->destroy($id);
            return response()->json(true,200);
          }
        }
        return response()->json(['error'=>'Person Not Found'],404);
    }
    public function downloadAllPersons(Request $request)
     {
        // $emailJob = new SendEmail();
        // dispatch($emailJob);

                /** Option 1 */
        Queue::push(new SendEmail());

        /** Option 2 */
        // dispatch(new MatchSendEmail($options));

        /** Option 3 */
        // (new MatchSendEmail($options))->dispatch();

        /** Option 4 */
        // \App\Jobs\SendEmail::dispatch();
        
        return response()->json([],200);
        $persons = Person::all();
         $data =  Person::transformPersonData($persons);
         $file = Carbon::now()->format('YmdHis').'personAllDetails.xlsx';
        
        // $sheets = new SheetCollection([
        //     'Person' => Person::where('id',$person_id)->get(),
        //     'PersonProfile' => PersonPersonalDetail::where('person_id',$person_id)->get(),
        //     'Bank Account'=> PersonBankAccountDetail::where('person_id',$person_id)->get(),
        //     'Location'=> PersonLocation::where('person_id',$person_id)->get(),
        //     'Kyc Detail'=> PersonKycDetail::where('person_id',$person_id)->get(),
        //     'Education'=> PersonEducation::where('person_id',$person_id)->get(),
        //     'Infrastructure'=> PersonInfrastructureDetail::where('person_id',$person_id)->get(),
        //     'Income'=> PersonIncome::where('person_id',$person_id)->get(),
        //     'Skills'=> PersonSkill::where('person_id',$person_id)->get(),
        //     'Work Experiences'=>PersonWorkExperience::where('person_id',$person_id)->get(),
        //     'Live Hood Engagement'=> PersonLiveHoodEngagement::where('person_id',$person_id)->get(),
        //     'Trainings'=> PersonTraining::where('person_id',$person_id)->get(),
        // ]);
        $filepath= (new FastExcel($data))->export(storage_path().'/'.$file);
        $url = "api/person/export/".$file;
       return response()->json(url($url),200);
         // return response()->json($data,200);
         //dd($persons);
     } 
     public function personCountLpCount(Request $request)
     {
         $checktoken = $request->header('checktoken');
      //  dd($checktoken);
        if($checktoken != '0fQgVCL1cM2mGytOSovz') {
           return response()->json('Token Invalid',402);
        }
        if(isset($request['VatikaId'])) {
        $persons = Person::where('geography_id',$request['VatikaId'])->get();

        }
        if(isset($request['VatikaName'])) {
         $geography = Geography::where('name',$request['VatikaName'])->first();
         if(!$geography) {
         return response()->json(['error'=>'Vatika Not Found'],404);
         }
         else {
            $request['VatikaId'] = $geography->id;
         }    
        }
         $persons = Person::where('geography_id',$request['VatikaId'])->get();
        
        $total_count = 0;
        foreach ($persons as $person) {
           $ledger = $person->ledger;
           $total_count += $ledger->balance; 

        }
        return response()->json(['person_count'=>count($persons),'total_lp'=>$total_count]);
     }
     public function allVatika(Request $request)
     {
         $checktoken = $request->header('checktoken');
          if($checktoken != '0fQgVCL1cM2mGytOSovz') {
           return response()->json('Token Invalid',402);
           }
         $geographies = Geography::all(['name','id']);
         return response()->json($geographies,200);
     }
     public function totalLp(Request $request)
     {
         $checktoken = $request->header('checktoken');
          if($checktoken != '0fQgVCL1cM2mGytOSovz') {
           return response()->json('Token Invalid',402);
           }
         $ledgers = Barter::where('status','Completed')->get();
         $total_lp = 0;
         foreach ($ledgers as $ledger) {
             $total_lp += $ledger->barter_total_lp_offered + $ledger->barter_total_lp_needed;
         }
         return response()->json($total_lp,200);
     }
     public function peopleAccountDetails(Request $request)
     {
         $checktoken = $request->header('checktoken');
          if($checktoken != '0fQgVCL1cM2mGytOSovz') {
           return response()->json('Token Invalid',402);
           }
         $persons = Person::all();
         // $res = collect();
         // foreach ($persons as $p) {
         //  // $p->geography_name  = $p->geographies->name;
         //   $res->push($p->load('bankAccount'));
         // }
         // //dd($res);
         // $fres = $res->groupBy('geography_name'); 
         return response()->json($persons->load('bankAccount','personPersonalDetails','personLocation','personKycDetail','personEducation','personInfrastructure','personIncome','personSkills','personWorkExperiences','personTrainings'),200); 
     }
     public function personDetailsByVatikaName(Request $request)
     {
         $checktoken = $request->header('checktoken');
          if($checktoken != '0fQgVCL1cM2mGytOSovz') {
           return response()->json('Token Invalid',402);
           }

         $geography = Geography::where('name',$request['VatikaName'])->first();
         if($geography) {
          $persons = Person::where('geography_id',$geography->id)->get()->load('bankAccount');
          return response()->json($persons,200);  
         }  
         return response()->json(['error'=>'Vatika Not Found'],404);
     }
     public function personRegistrationReport(Request $request)
     {
         $dms = DrishteeMitra::all();
         $res = collect();
         $mon = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
         $y = $request['year'];
         foreach ($dms as $dm) {
            $temp = array();
            $total =0;
            $temp['State'] = $dm->state->name;
            $mname = null;
            if($dm->middle_name) {
                $mname = $dm->middle_name; 
             } 
            $temp['State'] = $dm->dmGeography->geography->state? $dm->dmGeography->geography->state:'' ;
            $temp['District'] = $dm->dmGeography->geography->district? $dm->dmGeography->geography->district:'' ;
             $temp['Geography'] = $dm->dmGeography->geography->name;
                           
            $temp['Name'] = $dm->first_name.' '.$mname.' '.$dm->last_name;
            if($dm->is_csp) {
               $type = "CSP"; 
            }
            else if($dm->is_vaani) {
               $type = "Vaani"; 

            }
            else {
                $type = "Mitra";
            }
            $temp['Type'] = $type;
            // $temp['Name'] = $dm->first_name;
             $temp['Mobile'] = $dm->mobile;
             $temp['Bank Name'] = $dm->person && $dm->person->bankAccount ? $dm->person->bankAccount->bank_name:'';
             $temp['Payee Name'] = $dm->person && $dm->person->bankAccount ? $dm->person->bankAccount->payee_name:'';
             $temp['Account No'] = $dm->person && $dm->person->bankAccount ? $dm->person->bankAccount->account_number:'';
             $temp['IFSC Code'] = $dm->person && $dm->person->bankAccount ? $dm->person->bankAccount->ifsc_code:'';
             for($i = 0 ;$i< count($mon);$i++) {
                $c = 0;
                $re = Person::where('dm_id',$dm->id)->get();
                foreach ($re as $r) {
                    $dt = $r->created_at->format('M');
                    if($mon[$i] == $dt && $y == $r->created_at->format('Y')) {
                        $c++;
                    }
                }
                // Log::info("i".$i);
                $temp[$mon[$i].'-'.$y] = $c;
                $total += $c;

             }
             $temp['Total'] = $total;
             $res->push($temp);
             $temp = null;
         }
         $file = Carbon::now()->format('YmdHis').'dmreportmonthwise.xlsx';
        $filepath = (new FastExcel($res))->export(storage_path().'/'.$file);
        $url = "api/dmreportmonthwise/".$file;
       return response()->json(url($url),200);
         // return response()->json($res,200);
     }
     public function dmReportMonthWise($filename){
        $file = basename($filename);
        $filepath = storage_path().'/'.$file;
        return response()->download($filepath, $file, [
            'Content-Length: '. filesize($filepath)
        ]);
    }

    public function updateScript()
    {
        $persons = Person::all();
        foreach ($persons as $person) {
            $geography = Geography::find($person->geography_id);
            $person->state = $geography->state ? $geography->state:'';
            $person->district = $geography->district ? $geography->district:'';
            $person->block = $geography->block ? $geography->block:'';
            $person->save();

        }
    }
    public function updatebankdetails(Request $request)
    {
       $persons = Person::where('id','>',$request['start'])->where('id','<=',$request['end'])->get();
        // $persons = Person::where('id',39378)->get();
        
        // return response()->json($persons->load('bankAccount'),200);

        foreach ($persons as $person) {
              $bank = PersonBankAccountDetail::where('person_id',$person->id)->first();
              if($bank) {
                  $url = 'https://ifsc.razorpay.com/'.$bank->ifsc_code;
                  $crl = curl_init();
                  
                  curl_setopt($crl, CURLOPT_URL, $url);
                  curl_setopt($crl, CURLOPT_FRESH_CONNECT, true);
                  curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
                  $response = curl_exec($crl);
                  
                  if(!$response){
                      die('Error: "' . curl_error($crl) . '" - Code: ' . curl_errno($crl));
                  }
                  $httpcode = curl_getinfo($crl, CURLINFO_HTTP_CODE);
                  //Log::info("here id".$person->id);
                  curl_close($crl);
                  if($httpcode == 200) {
                    // return response()->json(json_decode($response),200);
                    // dd(json_decode($response)->BANK);
                    // dd($httpcode);
                    // $bankdetails = PersonBankAccountDetail::where('person_id',$person->id)->;
                    $bank->bank_name = json_decode($response)->BANK;
                    $bank->branch_address = json_decode($response)->ADDRESS;
                    $bank->branch_name = json_decode($response)->BRANCH;
                    $bank->save(); 
                    // dd() 
                  }
                  // dd($response); 
                   
              }
              
        }
        return response()->json($persons->load('bankAccount'),200);

                  
    }
    public function uploadVaccinationFile(Request $request,$id){
        $validation = Validator::make($request->all(),[
                'dose_1_file'      => 'sometimes',
                'dose_2_file'   => 'sometimes',
                'precaution_dose_file' => 'sometimes',
         ]);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,400);
        }
       $pc = Person::find($id);
       
       if($request->file('dose_1_file')) {
           $file = $request['dose_1_file']->getClientOriginalName();
           $extension = $request['dose_1_file']->getClientOriginalExtension(); 
           $filename = pathinfo($file, PATHINFO_FILENAME);
           $originalName = $filename.'.'.$extension; 
           $cover = $request->file('dose_1_file');
          // Storage::disk('public')->put($originalName,File::get($cover));
           $filePath = $request->file('dose_1_file')->storeAs('databackup/', $originalName, 'azure');
           
           $request['dose_1_certificate_name'] = $originalName;
          // $request['voter_id_photo_path'] = Storage::disk('public')->url($originalName);
           $request['dose_1_certificate_path'] = 'https://drishteedatastore.blob.core.windows.net/drishtee/databackup/'.$originalName;
           $pc->update($request->only(['dose_1_certificate_name','dose_1_certificate_path']));
       }
       if($request->file('dose_2_file')) {
           $file = $request['dose_2_file']->getClientOriginalName();
           $extension = $request['dose_2_file']->getClientOriginalExtension(); 
           $filename = pathinfo($file, PATHINFO_FILENAME);
           $originalName = $filename.'.'.$extension; 
           $cover = $request->file('dose_2_file');
          // Storage::disk('public')->put($originalName,File::get($cover));
           $filePath = $request->file('dose_2_file')->storeAs('databackup/', $originalName, 'azure');
           
           $request['dose_2_certificate_name'] = $originalName;
          // $request['voter_id_photo_path'] = Storage::disk('public')->url($originalName);
           $request['dose_2_certificate_path'] = 'https://drishteedatastore.blob.core.windows.net/drishtee/databackup/'.$originalName;
           $pc->update($request->only(['dose_2_certificate_name','dose_2_certificate_path']));
       }
       if($request->file('precaution_dose_file')) {
           $file = $request['precaution_dose_file']->getClientOriginalName();
           $extension = $request['precaution_dose_file']->getClientOriginalExtension(); 
           $filename = pathinfo($file, PATHINFO_FILENAME);
           $originalName = $filename.'.'.$extension; 
           $cover = $request->file('precaution_dose_file');
          // Storage::disk('public')->put($originalName,File::get($cover));
           $filePath = $request->file('precaution_dose_file')->storeAs('databackup/', $originalName, 'azure');
           
           $request['precation_dose_certificate_name'] = $originalName;
          // $request['voter_id_photo_path'] = Storage::disk('public')->url($originalName);
           $request['precaution_dose_certificate_path'] = 'https://drishteedatastore.blob.core.windows.net/drishtee/databackup/'.$originalName;
           $pc->update($request->only(['precation_dose_certificate_name','precaution_dose_certificate_path']));
       }
       return response()->json($pc,200);  
    }
    public function producerList(Request $request) {
         $persons = Person::all();
         $data = Person::transformAllData($persons,$request);
         $file = Carbon::now()->format('YmdHis').'allproducers.xlsx';
        // return response()->json($data,200);
        $filepath = (new FastExcel($data))->export(storage_path().'/'.$file);
        $url = "api/person/export/".$file;
        return response()->json(url($url),200);
    }
   public function reportFromFirstJantoThirdMarch() {
    $persons = Person::whereBetween('created_at',['2022-02-01','2022-03-03'])->get();
    $res = collect();
    foreach ($persons as $person) {
        $temp['first_name'] = $person->first_name;
          $temp['middle_name'] = $person->middle_name ? $person->middle_name : ''; 
          $temp['last_name'] = $person->last_name;
          $added_by = null;
          if($person->dm) {
            $added_by = $person->dm->first_name;
            if($person->dm->middle_name) {
             $added_by = $added_by.' '.$person->dm->middle_name;  
            }
            $added_by = $added_by.' '.$person->dm->last_name;
          }
          $temp['added_by'] = $added_by; 
          $temp['email'] = $person->email;
          $temp['mobile'] = $person->mobile;
          $temp['geography'] = $person->geographies ? $person->geographies->name: '';
          $temp['state'] = $person->state ? $person->state : '';
          $temp['district'] = $person->district ? $person->district: '';
          $temp['block'] = $person->block ? $person->block :'';
          $temp['dose_1'] = $person->dose_1;
          $temp['dose_2'] = $person->dose_2;
          $temp['type'] = ($person && $person->dm) ? $person->dm->type :'';
          $res->push($temp);
          $temp = null;

    }
    $file = Carbon::now()->format('YmdHis').'allproducersafterjan.xlsx';
        // return response()->json($data,200);
        $filepath = (new FastExcel($res))->export(storage_path().'/'.$file);
        $url = "api/person/export/".$file;
        return response()->json(url($url),200);
    // return response()->json($res,200);
   }
}
