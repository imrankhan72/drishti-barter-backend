<?php

namespace App\Http\Controllers;

use App\PersonService;
use Illuminate\Http\Request;
use App\Repositories\Repository\PersonServiceRepository;
use App\Http\Requests\PersonServiceRequest;
use App\ServiceSkillLevel;
use Validator;
use App\Geography;
use App\ServiceRateList;
use App\Person;

class PersonServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private $repository;
    public function __construct(PersonServiceRepository $repository)
    {
        $this->repository = $repository;
    }
    public function index()
    {
      return response()->json($this->repository->all()->load('service','person','geography','drishteeMitra'),200);    
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
            'geography_id'           => 'required',
            'geography_type'         => 'sometimes' ,
            'dm_id'                  => 'required',
            'person_id'              => 'required|exists:people,id',
            'service_id'             => 'required|exists:services,id',
            'service_lp'             => 'sometimes',
            'active_on_barterplace'  => 'required',
            'skill_level'            => 'required'
        ]);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors);
        }
        // $geography = Geography::find($request['geography_id']);
        // if($request['geography_type'] == 'state') {
        //   $state = State::find($geography->parent_id);
        //   //$srl = $state->stateRateList;
        //  $srl =  StateRateList::where('state_id',$state->id)->first();

        // }
        // else {

        // }

       $ps = PersonService::where('person_id',$request['person_id'])->where('service_id',$request['service_id'])->get();
       
       if(count($ps) == 0) {
        $person = Person::find($request['person_id']);
        // $person = Person::find($request['person_id']);
      
        $service_rate_list= ServiceRateList::where('state_id', $person->state_id)->first();
        if($request['skill_level'] == 'Skilled') {
            $service_lp = $service_rate_list->skilled;
        } 
        else if($request['skill_level'] == 'Highly Skilled') {
            $service_lp = $service_rate_list->highly_skilled;

        }
        else if($request['skill_level'] == 'Semi Skilled') {
            $service_lp = $service_rate_list->semi_skilled;

        }
        else if($request['skill_level'] == 'Unskilled') {
            $service_lp = $service_rate_list->onskilled;
        }
        else if($request['skill_level'] == 'Professionals') {
            $service_lp = $service_rate_list->professionals;
        }
        $request['service_lp'] = $service_lp;

        $ps =$this->repository->create($request->all());
        $template_id = 1207161761351644573;
        sendSMS('New service '.$ps->service->name.' with skill '.$request['skill_level'] .' has been added to your account by DM.',$person->mobile,$template_id);
       return response()->json($ps->load('service','person','geography','drishteeMitra'),201);
       } 
       else {
        return response()->json(['error'=>'This Service already added'],406);
       }
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\PersonService  $personService
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return response()->json($this->repository->findById($id)->load('service','person','geography','drishteeMitra'),200);
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\PersonService  $personService
     * @return \Illuminate\Http\Response
     */
    public function edit(PersonService $personService)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\PersonService  $personService
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
        $validation = Validator::make($request->all(),[
            'geography_id'           => 'required',
            'geography_type'         => 'sometimes' ,
            'dm_id'                  => 'required',
            'person_id'              => 'required',
            'service_id'             => 'required',
            'service_lp'             => 'sometimes',
            'active_on_barterplace'  => 'required'
        ]);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors);
        }
        $person = Person::find($request['person_id']);
        
        $service_rate_list= ServiceRateList::where('state_id', $person->state_id)->first();
        if($request['skill_level'] == 'Skilled') {
            $service_lp = $service_rate_list->skilled;
        } 
        else if($request['skill_level'] == 'Highly Skilled') {
            $service_lp = $service_rate_list->highly_skilled;

        }
        else if($request['skill_level'] == 'Semi Skilled') {
            $service_lp = $service_rate_list->semi_skilled;

        }
        else if($request['skill_level'] == 'Unskilled') {
            $service_lp = $service_rate_list->onskilled;
        }
        else if($request['skill_level'] == 'Professionals') {
            $service_lp = $service_rate_list->professionals;
        }
        $request['service_lp'] = $service_lp;
        $ps = $this->repository->update($request->all(),$id);
        $template_id = 1207161761358111822;
        sendSMS('Service '.$ps->service->name.' with skill <skill> has been updated.',$person->mobile,$template_id);
        return response()->json($ps->load('service','person','geography','drishteeMitra'),201);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\PersonService  $personService
     * @return \Illuminate\Http\Response
     */
    public function destroy(PersonService $personService)
    {
        //
    }

    /**
    * @param  \App\Person $product_id
    * @return \App\PersonService $p
    */
    public function personServiceGet($person_id)
    {
        $ps = PersonService::where('person_id',$person_id)->get();
        $res = collect();
        foreach ($ps as $p) {
            $p->service_name = $p->service && $p->service->name ? $p->service->name : null;
            $res->push($p); 
        }
      return response()->json($res,200);
    }
}
