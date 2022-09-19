<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BarterNeedLp extends Model
{
    protected $fillable = ['barter_id','lp'];

    protected $dates = ['created_at','updated_at'];
}
