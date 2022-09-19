<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DmMarginPercentage extends Model
{
    protected $fillable = ['dm_margin_percentage'];

    protected $dates = ['created_at','updated_at'];
    
}
