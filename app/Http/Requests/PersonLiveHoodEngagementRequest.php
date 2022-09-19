<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PersonLiveHoodEngagementRequest extends FormRequest
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
             'live_hood_engagements'         => 'sometimes',
            'description_of_le'              => 'sometimes',
            'group_activity_engagement'      => 'sometimes',
            'type'                           => 'sometimes',
            'association'                    => 'sometimes',
            'zone'                           => 'sometimes'
        ];
    }
}
