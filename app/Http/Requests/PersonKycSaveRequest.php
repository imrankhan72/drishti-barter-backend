<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PersonKycSaveRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'adhar_card_no'            => 'sometimes',
            'is_kyc_done'              => 'required' ,
            'adhar_card_photo_name'    => 'sometimes',
            'adhar_card_photo_path'    => 'sometimes',
            'pancard_no'               => 'sometimes'  ,
            'pancard_photo_name'       => 'sometimes',
            'pancard_photo_path'       => 'sometimes' ,
            'dl_no'                    => 'sometimes',
            'dl_photo_name'            => 'sometimes',
            'dl_photo_path'            => 'sometimes',
            'passport_no'              => 'sometimes',
            'passport_photo_name'      => 'sometimes',
            'passport_photo_path'      => 'sometimes',
            'voter_id_no'              => 'sometimes',
            'voter_id_photo_name'      => 'sometimes',
            'voter_id_photo_path'      => 'sometimes'
        ];
    }
}
