<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductSaveRequest extends FormRequest
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
            'name'                           =>'required',
            'default_livehood_points'        => 'required',
            'product_category_id'            => 'required|exists:product_categories,id',
            'calc_raw_material_cost'         => 'required',
            'calc_hours_worked'              => 'required',
            'calc_wage_applicable'           => 'required',
            'calc_margin_applicable'         => 'required', 
            'added_by_user_id'               => 'required',
            'is_gold_product'                => 'required',
            'is_branded_product'             => 'required|boolean',
            'mrp'                            => 'required',
            'availability'                   => 'required',
            'approved_by'                    => 'required:exists:users,id' ,
            'is_approved'                    => 'required',
            'photo_path'                     => 'required',
            'photo_name'                     => 'required',
            'approved_at'                    => 'required',
            'unit_id'                        => 'required' 
        ];
    }
}
