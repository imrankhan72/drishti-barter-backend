<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PersonKycDetail extends Model
{
    protected $fillable = ['adhar_card_no','is_kyc_done','adhar_card_photo_name','adhar_card_photo_path','pancard_no','pancard_photo_name','pancard_photo_path','dl_no','dl_photo_name','dl_photo_path','passport_no','passport_photo_name','passport_photo_path','voter_id_no','voter_id_photo_name','voter_id_photo_path','person_id'];
    protected $dates = ['created_at','updated_at'];

    public function person()
    {
    	return $this->hasOne('App\Person','person_id');
    }
}
