<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\Repository\ServiceRepository;
use App\Service;
use Validator;
use App\Http\Requests\ServiceRequest;
use Storage;
use File;
use App\ServiceSkillLevel;
use App\ServiceApplicableTime;
use Carbon\Carbon;
use Rap2hpoutre\FastExcel\FastExcel;
use Auth;
use App\ServiceCategory;
use App\ServiceAlias;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private $repository;

    public function __construct(ServiceRepository $repository)
    {
        $this->repository = $repository;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function filteredServices(Request $request)
    {
        $services = Service::filterServices($request->all());
        return response()->json($services,200);
    }
    public function index()
    {
        // dd("helo");
        return response()->json($this->repository->all()->load('serviceAlias','approvedBy','addedBy','serviceCategory','skillLevel','applicableTime'),200);
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
  //  dd($request['skill_level']);
        //
        $validation = Validator::make($request->all(),[
           'name'                  => "required",
            "default_livelihood_points" => "sometimes",
            "service_category_id"     => 'required|exists:service_categories,id',
             "added_by_user_id"=> "required",
              "approved_by"=>"sometimes",
              "is_approved"=> 'sometimes',
               "photo_path"=> 'sometimes',
                "photo_name"=> 'sometimes',
                "approved_at"=> "sometimes",
                "skill_level"  => 'required|array',
               // 'skill_level.*' => 'required|string|distinct|max:255',
                "applicable_time" => 'required|array',
               // "applicable_time.*"=>'required|string|distinct|max:255'
        ]);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,400);
        }
        $service = $this->repository->create($request->all());
        foreach ($request['skill_level'] as $sk) {
         //   dd($sk);
        $sskill = $service->skillLevel()->create($sk);
                    
        }
        foreach ($request['applicable_time'] as $at) {
        $s_applicabletime = $service->applicableTime()->create($at); 
                    
        }
        return response()->json($service->load('serviceCategory','serviceAlias','skillLevel','applicableTime','addedBy'), 201);

    }
    /**
     * Display the specified resource.
     *
     * @param  \App\State  $state
     * @return \Illuminate\Http\Response
     */
    public function show(Service $service)
    {
        return response()->json($this->repository->findById($service->id)->load('serviceAlias','approvedBy','addedBy','serviceCategory','skillLevel','applicableTime'),201);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\State  $state
     * @return \Illuminate\Http\Response
     */
    public function edit(Service $service)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\State  $state
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Service $service){

        $validation = Validator::make($request->all(),[
           'name'                  => "required",
            "default_livelihood_points" => "sometimes",
            "service_category_id"     => 'required|exists:service_categories,id',
             "added_by_user_id"=> "required",
              "approved_by"=>"sometimes",
              "is_approved"=> 'sometimes',
               "photo_path"=> 'sometimes',
                "photo_name"=> 'sometimes',
                "approved_at"=> "sometimes",
                "skill_level"  => 'required|array',
               // 'skill_level.*' => 'required|string|distinct|max:255',
                "applicable_time" => 'required|array',
               // "applicable_time.*"=>'required|string|distinct|max:255'
        ]);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,400);
        }
        $service = $this->repository->update($request->only('added_by_user_id','name','service_category_id'),$service->id);
        $sks = $service->skillLevel;
        foreach($sks as $sk) {
            ServiceSkillLevel::destroy($sk->id);
        }
        $ats = $service->applicableTime;
        foreach ($ats as $at) {
            ServiceApplicableTime::destroy($at->id);
        }
        foreach ($request['skill_level'] as $sk) {
         //   dd($sk);
        $sskill = $service->skillLevel()->create($sk);
                    
        }
        foreach ($request['applicable_time'] as $at) {
        $s_applicabletime = $service->applicableTime()->create($at); 
                    
        }
        return response()->json($service->load('serviceCategory','serviceAlias','skillLevel','applicableTime','addedBy'), 201);
     //   return response()->json($this->repository->update($request->all(), $service->id), 201);
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\State  $state
     * @return \Illuminate\Http\Response
     */
    public function destroy(Service $service)
    {
        $service = $this->repository->changeActiveStatus($service->id);
        return response()->json($service,200);
    }

    /**
     * @param Illuminate\Http\Request $request
     * do store serviceAlias related to service
     * 
     */
    public function serviceAlias(Request $request){
        $validation = Validator::make($request->all(),[
              'service_id'             => 'required|exists:services,id',
              'service_translation'    => 'required',
              'language'               => 'required'
        ]);
        if($validation->fails()) {
            $error = $validation->errors();
            return response()->json($error,400);
        }
        $pp = Service::find($request['service_id']);
        if($pp) {
            $pa = $pp->serviceAlias()->create($request->all());
            return response()->json($pa,201);
        }
        return response()->json(['error'=>'Product Not Found'],404);
    }

    /**
     * @param Illuminate\Http\Request $request
     * @param App\Service $id
     * do upload service image related to $id
     * 
     */
    public function uploadImage(Request $request,$id){
        // dd($request['file']);
       //$this->authorize('create',StaffBasicDetails::class);
       $validation = Validator::make($request->all(),[
            'file' => 'required|file|mimes: jpg,jpeg,png,bmp|max:10000'
        ]);
        if($validation->fails()){
            $errors = $validation->errors();
            return response()->json($errors, 400);
        }
       $pc = Service::find($id);
       $file = $request['file']->getClientOriginalName();
       $extension = $request['file']->getClientOriginalExtension(); 
       $filename = pathinfo($file, PATHINFO_FILENAME);
       $originalName = $filename.'.'.$extension; 
       $cover = $request->file('file');
       Storage::disk('public')->put($originalName,File::get($cover));
       $request['icon_name'] = $originalName;
       $request['icon_path'] = Storage::disk('public')->url($originalName);
       $pc->update($request->only(['icon_name','icon_path']));
       return response()->json($pc,201); 
    }

    /**
     * @param Illuminate\Http\Request $request
     * do import services, ServiceSkillLevel, ServiceApplicableTime, ServiceAlias
     * 
     */
    public function importServices(Request $request){

        $regex = "/^(?=.+)(?:[1-9]\d*|0)?(?:\.\d+)?$/";
        $validation = Validator::make($request->all(),[
            "*.name"                  => "required",
            "*.service_category_name" => 'required|string',
            "*.skill_level"           => 'required|array',
            "*.applicable_time"       => 'required|array',
        ]);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,400);
        }       


        $coll_insert = collect();
        $coll_update = collect();
        $coll_alias = collect();
        $coll_skill_level = collect();
        $coll_applicable_t = collect();

        foreach ($request->all() as $data) {
            $service = Service::where('name',$data['name'])->first();
            
            $sc = ServiceCategory::where('name',$data['service_category_name'])->first();
            if(!$sc){
                $scData['name'] = $data['service_category_name'];
                $sc = ServiceCategory::create($scData);
            }

            $data['service_category_id'] = $sc->id;

            if($service){
                $service->update($data);
                $coll_update->push($service);
            }else{
                // $user = Auth::User();
                // $data['added_by_user_id'] = $user->id;
                $data['added_by_user_id'] = 1;
                $service = Service::create($data);
                $coll_insert->push($service);
            }

            foreach ($data['skill_level'] as $sk) {
                $sl = ServiceSkillLevel::where('skill_level',$sk)->where('service_id',$service->id)->first();
                if(!$sl){
                    $sks['service_id'] = $service->id;
                    $sks['skill_level'] = $sk;
                    $sl = ServiceSkillLevel::create($sks);
                    $coll_skill_level->push($sl);
                }
            }
                                  
            foreach ($data['applicable_time'] as $at) {
                $sat = ServiceApplicableTime::where('applicable_time',$at)->where('service_id',$service->id)->first();
                if(!$sat){
                    $ata['service_id'] = $service->id;
                    $ata['applicable_time'] = $at;
                    $sat = ServiceApplicableTime::create($ata);
                    $coll_applicable_t->push($sat);
                }
            }

            if(!empty($data['service_translation']) && !empty($data['language']) && (sizeof($data['service_translation'])) == sizeof($data['language'])) {
                $array_serTran = $data['service_translation'];
                $array_lan = $data['language'];
                $i=0;
                foreach ($array_serTran as $st) {
                    $pa = ServiceAlias::where('service_id',$service->id)->where('language',$array_lan[$i])->where('service_translation',$st)->first();
                    if(!$pa){
                        $paa['service_id'] = $service->id;
                        $paa['service_translation'] = $st;
                        $paa['language'] = $array_lan[$i];
                        $pa = ServiceAlias::create($paa);
                        $coll_alias->push($pa);
                    }
                    $i++;
                }                
           }
        }

        return response()->json(["Services Insert"=>$coll_insert,"Services Update"=>$coll_update,"Services Alias"=>$coll_alias,"Services Applicable Time"=>$coll_applicable_t,"Services Skill Level"=>$coll_skill_level],200);
    }

    /**
     * @return download service file export url
     * 
     */
    public function exportServices(){
        $services = Service::all();
        $file = Carbon::now()->format('YmdHis').'services.xlsx';
        $filepath = (new FastExcel($services))->export(storage_path().'/'.$file);
        $url = "api/servicesexport/".$file;
        return response()->json(url($url),200);
    }

    /**
     * @return export file
     * 
     */
    public function downloadExportServices($filename){
        $file = basename($filename);
        $filepath = storage_path().'/'.$file;
        return response()->download($filepath, $file, [
            'Content-Length: '. filesize($filepath)
        ]);
    }

    /**
     * @return download service sample file url
     * 
     */
    public function importSamplefile(){
        $file = 'ServiceSamplefile.xlsx';
        $url = "api/service/samplefile/".$file;
        return response()->json(url($url),200);
    }

    /**
     * @return export sample file
     * 
     */
    public function downloadImportSamplefile($filename){
        $file = basename($filename);
        $filepath = storage_path().'/'.$file;
        return response()->download($filepath, $file, [
            'Content-Length: '. filesize($filepath)
        ]);
    }
    public function deleteService($id)
    {
        $service = Service::find($id);
        if($service) {
          $salias = $service->serviceAlias;
          $skilllevel = $service->skillLevel;
          $sapplicable_time = $service->applicableTime;
          $ps = $service->personService;
          $bns = $service->barterNeedService;
          $bmlis = $service->barterMatchLocalInventoryServices;
          if(count($salias) > 0 || count($skilllevel) > 0 || count($sapplicable_time) > 0 || count($ps) > 0 || count($bns) > 0 || count($bmlis) > 0) {
            
                return response()->json(['error'=>'you can not delete this Service'],400);

          }
          else {
            $service->destroy($id);
            return response()->json(true,200);
          }
        }
        return response()->json(['error'=>'Service Not Found'],404);
    }
}