<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MarginPercentage extends Model
{
    protected $fillable = ['margin_percentage'];

    protected $dates= ['created_at','updated_at'];
}
