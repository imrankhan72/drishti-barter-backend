<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PersonBankAccountDetailsSaveRequest extends FormRequest
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
            'account_number'   => 'required',
            'bank_name'        => 'required',
            'ifsc_code'        => 'required',
            'payee_name'       => 'required',
        ];
    }
}
