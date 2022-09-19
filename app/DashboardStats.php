<?php

namespace App;
 
use Illuminate\Database\Eloquent\Model;

class DashboardStats extends Model
{
    protected $fillable = ['vatika','mitras', 'persons', 'products', 'completed_barters', 'completed_barter_lp' ,'completed_barter_geo' ,'open_barters', 'open_barter_lp', 'open_barter_geo', 'tejas_products', 'average_products','average_services', 'producers_with_no_product', 'average_no_of_people_with_dm','dm_with_no_people', 'producers_with_lp_in_account', 'producers_with_no_lp_in_account','csp_count','mitra_count','ceep_count','others_count','vaccinated','d1_vaccinated','d2_vaccinated'];

    protected $table = 'dashboard_stats';

    protected $dates = ['created_at','updated_at'];
}
