<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PersonLocationSaveRequest extends FormRequest
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
           'state'           => 'sometimes',
           'city'            => 'sometimes',
           'block'           =>'sometimes',
           'village'         => 'sometimes',
           'pincode'         => 'sometimes',
           'latitude'        => 'sometimes' ,
           'longitude'       => 'sometimes',
           'area_type'       => 'sometimes'
        ];
    }
}
