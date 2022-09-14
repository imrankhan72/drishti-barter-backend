<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PeopleSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:name';

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
    
    // $persons
    // foreach ($pe as $value) {
            
    //     }    
    // $data['template']['acceleration_retardation_limit'] = $setting['acceleration_retardation_limit'];
    // $url = 'http://api.nift.score.cobold.xyz/api/readingscore/'.$testsheet->id.'/';
    // $ch = curl_init($url);
    // $payload = json_encode($data);
    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // curl_setopt($ch, CURLINFO_HEADER_OUT, true);
    // curl_setopt($ch, CURLOPT_POST, true);
    // curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

    // // Set HTTP Header for POST request 
    // curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    // 'Content-Type: application/json',
    // 'Content-Length: ' . strlen($payload))
    // );

    // // Submit the POST request
    // $result = curl_exec($ch);
    // // Close cURL session handle
    // curl_close($ch);
    // return json_decode($result);
    }
}
