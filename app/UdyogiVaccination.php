<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UdyogiVaccination extends Model
{
    protected $fillable = ['state_id','state_name','district_id','district_name','udyogi_count','dose_1_count','dose_2_count'];

    protected $dates = ['created_at','updated_at'];
    
}
