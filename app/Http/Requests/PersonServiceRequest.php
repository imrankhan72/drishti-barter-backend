<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PersonServiceRequest extends FormRequest
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
            'geography_id'           => 'required',
            'geography_type'         => 'sometimes' ,
            'dm_id'                  => 'required',
            'person_id'              => 'required',
            'service_id'             => 'required',
            'service_lp'             => 'sometimes',
            'active_on_barterplace'  => 'required'
        ];
    }
}
