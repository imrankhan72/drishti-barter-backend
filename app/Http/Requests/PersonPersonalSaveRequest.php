<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PersonPersonalSaveRequest extends FormRequest
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
            'dob'             => 'sometimes',
            'marital_status'  => 'sometimes' ,
            'gender'          => 'sometimes',
            'disability'      => 'sometimes',
            'religion'        => 'sometimes',
            'caste'           => 'sometimes',
            'otp'             => 'sometimes',
            'language'        => 'sometimes',
            'photo_name'      => 'sometimes',
            'photo_path'      => 'sometimes'
        ];
    }
}


