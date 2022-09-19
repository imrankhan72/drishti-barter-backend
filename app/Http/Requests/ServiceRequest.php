<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceRequest extends FormRequest
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
            'name'                  => "required",
            "default_livelihood_points" => "required",
            "service_category_id"     => 'required|exists:service_categories,id',
             "added_by_user_id"=> "required",
              "approved_by"=>"sometimes",
              "is_approved"=> 'sometimes',
               "photo_path"=> 'sometimes',
                "photo_name"=> 'sometimes',
                "approved_at"=> "sometimes",
                "skill_level"  => 'required|array',
                'skill_level.*' => 'required|string|distinct|max:255',
                "applicable_time" => 'required|array',
                "applicable_time.*"=>'required|string|distinct|max:255'
        ];
    }
}
