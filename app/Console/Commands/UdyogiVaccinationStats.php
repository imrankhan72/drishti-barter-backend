<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\State;
use App\District;
use App\Person;
use App\UdyogiVaccination;

class UdyogiVaccinationStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'udyogi:stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
    }
}
