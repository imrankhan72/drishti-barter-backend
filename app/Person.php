<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // <-- This is required
use DB;

class Person extends Model
{
	use SoftDeletes;
	
    protected $table = 'people';
    protected $fillable = ['first_name','middle_name','last_name','email','mobile','geography_id','geography_type','ledger_id','otp','status','dm_id','add_by_user_id','otp','added_on','is_profile_complete','available_lp','state_id','block','state','district','vaccinated','dose_1_certificate_name','dose_1_certificate_path','dose_2_certificate_name','dose_2_certificate_path','precation_dose_certificate_name','precaution_dose_certificate_path','dose_1','dose_2','precaution_dose'];

     protected $dates = ['created_at','updated_at','deleted_at']; 

    public function geographies()
    {
    	return $this->belongsTo('App\Geography','geography_id');
    }
    public function state()
    {
        return $this->belongsTo('App\State','state_id');
    }

    public function dm()
    {
       return $this->belongsTo('App\DrishteeMitra','dm_id'); 
    }
    // public function ledgers()
    // {
    // 	return $this->hasOne('App\Ledger','ledger_id');
    // }
    public function bankAccount()
    {
        return $this->hasOne('App\PersonBankAccountDetail','person_id');
    }
    public function personPersonalDetails()
    {
        return $this->hasOne('App\PersonPersonalDetail','person_id');
        
    }
    public function personLocation()
    {
        return $this->hasOne('App\PersonLocation','person_id');
    }
    public function personKycDetail()
    {
        return $this->hasOne('App\PersonKycDetail','person_id');
        
    }
    public function personEducation()
    {
        return $this->hasOne('App\PersonEducation','person_id');
        
    }
    public function personInfrastructure()
    {
        return $this->hasOne('App\PersonInfrastructureDetail','person_id');
        
    }
    public function personIncome()
    {
        return $this->hasOne('App\PersonIncome','person_id');
        
    }
    public function personSkills()
    {
        return $this->hasMany('App\PersonSkill','person_id');
    }
    public function personWorkExperiences()
    {
        return $this->hasMany('App\PersonWorkExperience','person_id');
        
    }
    public function personLiveHoodEngagement()
    {
        return $this->hasOne('App\PersonLiveHoodEngagement','person_id');
        
    }
    public function personTrainings()
    {
        return $this->hasMany('App\PersonTraining','person_id');
        
    }
    public function personProducts()
    {
        return $this->hasMany('App\PersonProduct','person_id');
        
    }
    public function personServices()
    {
        return $this->hasMany('App\PersonService','person_id');
        
    }
    public function barters()
    {
        return $this->hasMany('App\Barter','person_id');
        
    }
    public function barterConfirmation()
    {
        return $this->hasMany('App\BarterConfirmation','person_id');
    }
    public function barterMatching()
    {
        return $this->hasMany('App\BarterMatch','barter_id');
    }
    public function ledger(){
        return $this->belongsTo('App\Ledger', 'ledger_id');
    }
    public function ledgers()
    {
        return $this->morphMany(Ledger::class, 'ledger')->orderBy('id','DESC');
    }
    public function personBan()
    {
        return $this->hasOne('App\PersonBan','person_id');
    }
    public function vaccination()
    {
      return $this->hasOne('App\Vaccination','person_id');
    }

    /**
     *
     * @param  $type Success/Fail
     * @param  $balance transaction 
     * @return true
     * do store user transaction details
     */
    public function createPersonTransaction($type,$balance){
    // $user = Auth::User();
      $ledger = new Ledger();
      // $ledger->ledger_id = $id;
      $ledger->ledger_type = $type;
      $ledger->balance = $balance;
      // $log->user_id = $user->id;
      // $log->is_delete = $isdelete;
      $this->ledgers()->save($ledger);
      return true;   
    }
    
    public function ledgerTransactions()
    {
        return $this->morphMany(LedgerTransaction::class, 'ledgerTransaction')->orderBy('id','DESC');
        
    }

    /**
    *
    * @param  \Illuminate\Http\Request $request
    * @return \App\Person $person
    * do filter person depend upon name, email, mobile, created_at
    */
    public static function filterPersons($request){
        $persons = DB::table('people')->where('deleted_at','=',null);
        if(isset($request['geography_ids'])) {
        $persons->whereIn('geography_id',$request['geography_ids']);
        }
        if(isset($request['filters'])) {
            $filters = $request['filters'];
            foreach($filters as $key => $value) {
                if($key == 'name') {
                $searchname = explode(' ', $value);
                $first_name ='';
                $last_name='';
                if(count($searchname) >1 && count($searchname) <= 2){                    
                    $first_name = $searchname[0];
                    $last_name = $searchname[1];
                    $persons = $persons->where(function($query) use($first_name, $last_name) {
                        $query->where('first_name','like', '%'.$first_name.'%')
                            ->where('last_name','like','%'.$last_name.'%')
                            ->orWhere(function($query2) use($first_name, $last_name){
                                $first_name = $first_name . ' '. $last_name;
                                $query2->where('first_name','like','%'.$first_name.'%');
                            });
                    });
                }
                elseif(count($searchname) == 3) {
                    $first_name = $searchname[0]. ' '.$searchname[1];
                    $last_name =  $searchname[2];
                    $persons = $persons->where(function($query) use($first_name, $last_name) {
                                    $query->where('first_name','like','%'.$first_name.'%')
                                        ->orWhere('last_name','like','%'.$last_name.'%');
                                });
                }
                else {
                    $persons = $persons->where(function($query) use ($value){
                        $query->where('first_name','like','%'.$value.'%')
                                ->orWhere('last_name','like','%'.$value.'%');
                        });
                    }
                }
                
                else if($key == 'email' || $key == 'mobile'){
                    if($value == '--') {
                        $persons = $persons->where(function($query) use($key, $value) {
                            $query->where($key,'=','')->orWhereNull($key);   
                        });
                    }
                    else {
                        $persons = $persons->where($key,'like','%'.$value.'%');
                    }
                }
                else if($key == 'geography_id') {
                   //$persons =  $persons->where(function($query) use ($key,$value) {
                    $persons = $persons->where('geography_id',$value);

                  // });

                }
               else if($key == 'state') {
                   //$persons =  $persons->where(function($query) use ($key,$value) {
                    $persons = $persons->where('state',$value);

                  // });

                }else if($key == 'district') {
                   //$persons =  $persons->where(function($query) use ($key,$value) {
                    $persons = $persons->where('district',$value);

                  // });

                }else if($key == 'block') {
                   //$persons =  $persons->where(function($query) use ($key,$value) {
                    $persons = $persons->where('block',$value);

                  // });

                }
               else if($key == 'dm_ids') {
                  // $dm_ids = $request['filters']['dm_ids'];
                  $persons = $persons->whereIn('dm_id',$value);
                 }
    //            else if($key == 'state' || $key == 'district') {
    //               $persons->join('geographies', 'geographies.id', '=', 'people.geography_id')
    // ->where('geographies.state', $value);
    //                // $pgeo = $persons->geographies->where($key,$value);

    //             // $persons = $persons->whereHas('geographies', function($query) use($key,$value) {
    //             //      $query->where($key,'like','%'.$value.'%');
    //             // });
    //            }  
                else if($key == 'created_at' || $key == 'updated_at') {
                    $start_date = isset($value['start_date']) ? $value['start_date'] : null;
                    $end_date = isset($value['end_date']) ? $value['end_date'] : null;
                    if($start_date && $end_date) {
                        $persons = $persons->whereBetween($key, array($start_date, $end_date));
                    }
                }
                
            }
        }
        
        // dd($persons->toSql());
        
       
        if(isset($request['filters']['sortBy']) && !empty($request['filters']['sortBy'])) {
            $sortBy = $request['filters']['sortBy'];
            foreach($sortBy as $key => $value) {
                if($key == 'name') {
                    $persons->orderBy('first_name',$value);
                }
                else {
                    $persons->orderBy($key, $value);
                }
            }
        }
        else {
            $persons->orderBy('id', 'DESC');
        }
        
        if(isset($request['count']) && $request['count']) {
            $persons = $persons->get();
            return count($persons);
        }
        $offset = isset($request['skip']) ? $request['skip'] : 0 ;
        $chunk = isset($request['skip']) ? $request['limit'] : 999999;
        $persons = $persons->skip($offset)->limit($chunk)->get();
        $personsCollection = collect();
        foreach($persons as $person) {
            $c = Person::find($person->id);
            // $c->remarks = $c->remarks ? $c->remarks()->latest()->first() : null;
            // $c->geographies->where('state','like','%'.$request['state'].'%');
            $c->load('geographies','bankAccount','personPersonalDetails','personLocation','personKycDetail','personEducation','personInfrastructure','personIncome','personSkills','personWorkExperiences','personLiveHoodEngagement','personTrainings','ledgers','dm','ledger');
            $personsCollection->push($c);
        }
        return $personsCollection;
    }
    public static function transformPersonData($persons)
    {
        $data = collect();
        foreach ($persons as $person) {
            // 'first_name','middle_name','last_name','email','mobile','geography_id','geography_type','ledger_id','otp','status','dm_id','add_by_user_id','otp','added_on','is_profile_complete','available_lp','state_id'
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
          $temp['state'] = $person->geographies ? $person->geographies->state : '';
          $temp['district'] = $person->geographies ? $person->geographies->district: '';
          $temp['ac_no'] = $person->bankAccount ? $person->bankAccount->account_number : '';
          $temp['bank_name'] = $person->bankAccount ?  $person->bankAccount->bank_name : '';

          $temp['ifsc_code'] = $person->bankAccount ? $person->bankAccount->ifsc_code:'';
          $temp['payee_name'] = $person->bankAccount ? $person->bankAccount->payee_name:'';
          $temp['digital_literacy'] = $person->personEducation ? $person->personEducation->digital_literacy:'';
          $temp['max_qualification'] = $person->personEducation ? $person->personEducation->max_qualification:'';
          $temp['monthly_income'] = $person->personIncome ? $person->personIncome->monthly_income:'';
          $temp['bpl_card_holder'] = $person->personIncome ? $person->personIncome->bpl_card_holder:'';
          $temp['income_type'] = $person->personIncome ? $person->personIncome->income_type:'';
          $temp['adhar_card_no'] = $person->personKycDetail ? $person->personKycDetail->adhar_card_no:'';
          $temp['pancard_no'] = $person->personKycDetail ? $person->personKycDetail->pancard_no:'';
          
          $temp['dl_no'] = $person->personKycDetail ? $person->personKycDetail->dl_no:'';
          $temp['passport_no'] = $person->personKycDetail ? $person->personKycDetail->passport_no:'';
          $temp['voter_id_no'] = $person->personKycDetail ? $person->personKycDetail->voter_id_no:'';
          $temp['total_land_holding'] = $person->personInfrastructure ? $person->personInfrastructure->total_land_holding:'';

          // $temp['irrigation_facilities'] = $person->personInfrastructure ? $person->personInfrastructure->total_land_holding:'';
          // $temp['cultivable_land'] = $person->personInfrastructure ? $person->personInfrastructure->cultivable_land:'';
          // $temp['crop_mapping']= $person->personInfrastructure ? $person->personInfrastructure->crop_mapping:'';
          // $temp['livestock'] =$person->personInfrastructure ? $person->personInfrastructure->livestock:'';
          // $temp['house_type']= $person->personInfrastructure ? $person->personInfrastructure->house_type:'';
          // $temp['vehicles'] = $person->personInfrastructure ? $person->personInfrastructure->vehicles:'';
          // $temp['own_house'] = $person->personInfrastructure ? $person->personInfrastructure->own_house:'';
          // $temp['storage_space'] = $person->personInfrastructure ? $person->personInfrastructure->storage_space:'';
          // $temp['construction_material'] = $person->personInfrastructure ? $person->personInfrastructure->construction_material:'';
          // $temp['machines']= $person->personInfrastructure ? $person->personInfrastructure->machines:'';
          // $temp['farming_equipment']= $person->personInfrastructure ? $person->personInfrastructure->farming_equipment:'';
          // $temp['dob']= $person->personPersonalDetails ? $person->personPersonalDetails->dob:'';
          // $temp['marital_status'] = $person->personPersonalDetails ? $person->personPersonalDetails->marital_status:'';
          // $temp['gender'] = $person->personPersonalDetails ? $person->personPersonalDetails->gender:'';
          // $temp['disability'] = $person->personPersonalDetails ? $person->personPersonalDetails->disability:'';
          // $temp['religion'] = $person->personPersonalDetails ? $person->personPersonalDetails->religion:'';
          // $temp['caste'] = $person->personPersonalDetails? $person->personPersonalDetails->caste:'';
          // $temp['language'] = $person->personPersonalDetails ? $person->personPersonalDetails->language:'';
          $data->push($temp);
        }
        return $data;
    }
    public static function transformAllData($persons,$request) {
      $res = collect();
      foreach ($persons as $person) {
        $y = $person->created_at->format('Y');
        $m = $person->created_at->format('m');
        // dd($m);
        if($y == $request['year'] && $request['month'] == $m ) {
         $temp['State'] = $person->state;
        $temp['District'] = $person->district;
        $temp['Block'] = $person->block;
        $temp['Geography'] = $person->geographies->name;
        $temp['Name'] = $person->first_name.' '.$person->middle_name ? $person->middle_name : ''.$person->last_name;
          $added_by = null;
          if($person->dm) {
            $added_by = $person->dm->first_name;
            if($person->dm->middle_name) {
             $added_by = $added_by.' '.$person->dm->middle_name;  
            }
            $added_by = $added_by.' '.$person->dm->last_name;
          }
          $temp['Added By'] = $added_by;
          $temp['Type'] = $person->dm && $person->dm->type ? $person->dm->type:'';
          $temp['Vaccination'] = $person->vaccinated == true? 'Yes':'No';
          $temp ['Dose 1'] = $person->dose_1_certificate_name!=null ? '1':'0';
          $temp['Dose 2'] =   $person->dose_2_certificate_name!=null ? '1':'0';
          $temp['Precaution Dose'] = $person->precation_dose_certificate_name !=null ?'1':'0';
          $temp['Account No'] = $person->bankAccount && $person->bankAccount->account_number ?$person->bankAccount->account_number: '';
          $temp['Payee Name'] = $person->bankAccount && $person->bankAccount->payee_name ? $person->bankAccount->payee_name:'';
          $temp['Bank'] = $person->bankAccount && $person->bankAccount->bank_name? $person->bankAccount->bank_name:'';
          $temp['IFSC Code'] = $person->bankAccount && $person->bankAccount->ifsc_code? $person->bankAccount->ifsc_code:'';
          $res->push($temp);
          $temp = null;  
        }
        

      }
      return $res;
    }
}
