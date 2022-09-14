<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PersonInfrastructureRequest extends FormRequest
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
            'total_land_holding'        => 'sometimes',
            'irrigation_facilities'     => 'sometimes',
            'cultivable_land'           => 'sometimes',
            'crop_mapping'               => 'sometimes' ,
            'livestock'                 => 'sometimes',
            'house_type'                => 'sometimes',
            'vehicles'                  => 'sometimes' ,
            'own_house'                 => 'sometimes',
            'storage_space'             => 'sometimes',
            'construction_material'     => 'sometimes',
            'machines'                  => 'sometimes',
            'farming_equipment'         => 'sometimes'
        ];
    }
}
