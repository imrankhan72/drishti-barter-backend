<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PersonSaveRequest extends FormRequest
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
            'first_name'           => 'required',
            'middle_name'          => 'sometimes',
            'last_name'            => 'required', 
            'email'                => 'required|string|email|max:255|unique:people',
            'mobile'               => 'required|string|max:255|unique:people',
            'geography_id'         => 'required|exists:geographies,id',
            'geography_type'       => 'required' ,
            'ledger_id'            => 'sometimes',
            'status'               => 'sometimes',
            'otp'                  => 'sometimes',
            'dm_id'                => 'sometimes|required|exists:drishtree_mitras,id',
            'added_by_user_id'     => 'sometimes|required|exists:users,id'
        ];
    }
}
