<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ServiceRateList;
use App\Person;
use App\Repositories\Repository\ServiceRateListRepository;
use Validator;
use Storage;
use File;
use Carbon\Carbon;
use Rap2hpoutre\FastExcel\FastExcel;


class ServiceRateListController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private $repository;
    public function __construct(ServiceRateListRepository $repository)
    {
        $this->repository = $repository;
    }
    public function index()
    {
        return response()->json($this->repository->all(),200);
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
        return response()->json($this->repository->create($request->all()),201);
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        return response()->json($this->repository->update($request->all(),$id),201);
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }


     /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @return List of object ServiceRateList
     * Update the array resource in storage.
     */
    public function updateAll(Request $request){

        $regex = "/^(?=.+)(?:[1-9]\d*|0)?(?:\.\d+)?$/";

        $formData = ['data'=>$request->all()];

        $validation = Validator::make($formData,[
            "data.*.id"             => "required|exists:service_rate_lists,id",
            "data.*.state_id"       => "required|exists:states,id",
            "data.*.highly_skilled" => array('required','regex:'.$regex),
            "data.*.skilled"        => array('required','regex:'.$regex),
            "data.*.semi_skilled"   => array('required','regex:'.$regex),
            "data.*.professionals_ratio"  => array('required','regex:'.$regex),
            "data.*.highly_skilled_ratio"  => array('required','regex:'.$regex),
            "data.*.skilled_ratio"  => array('required','regex:'.$regex),
            "data.*.semi_skilled_ratio"  => array('required','regex:'.$regex),
            "data.*.onskilled_ratio"  => array('sometimes'),
            
        ]);

        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,400);
        }

        foreach ($request->all() as $serviceRateListData) {
            
            $serviceRateList = ServiceRateList::find($serviceRateListData['id']);
            if($serviceRateList){
                $serviceRateList->update($serviceRateListData);
            }
        }

        $persons = Person::all();
        foreach ($persons as $person) {
            foreach ($person->personServices as $ps) {
                $request['state_id'] = $person->state_id;
                $request['skill_level'] = $ps->skill_level;
                $lp = $this->calculateLp($request,true);
                $ps->service_lp = $lp;
                $ps->save();
            }
        }

        return response()->json(ServiceRateList::all(),201);
    }

    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @return $service_lp
     * calculate service_lp.
     */
    public function calculateLp(Request $request,$check = false)
    {
        $service_rate_list = ServiceRateList::where('state_id',$request['state_id'])->first();
        $service_lp = 0.0;
        if($service_rate_list) {
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
        if($check){
            return $service_lp;
        }else{
            return response()->json(["service_lp"=>$service_lp],200);
        }
      //  dd($service_lp);
      //  $service_lp = $service_lp*$request['no_of_days'];
        }
    }

    /**
     *
     * @return download url of exported serviceratelist file
     * generate file and return download url.
     */
    public function exportServiceRateList(){
        $serviceratelist = ServiceRateList::all();
        $file = Carbon::now()->format('YmdHis').'serviceratelist.xlsx';
        $filepath = (new FastExcel($serviceratelist))->export(storage_path().'/'.$file);
        $url = "api/serviceratelistexport/".$file;
        return response()->json(url($url),200);
    }

    /**
     *
     * @return serviceratelist file
     * 
     */
    public function downloadExportServiceRateList($filename){
        $file = basename($filename);
        $filepath = storage_path().'/'.$file;
        return response()->download($filepath, $file, [
            'Content-Length: '. filesize($filepath)
        ]);
    }
}
