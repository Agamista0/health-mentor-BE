<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdminRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'required|min:2',
            'email' => 'required|email|unique:admins',
            'password' => 'required',
        ];
    }
    public function messages(){
        return [
            'name.required' => __('words.name_require_validation'),
            'phone.required' => __('words.phone_require_validation'),
            'email.required' => __('words.email_require_validation'),
            'phone.numeric' => __('words.phone_numeric_validation'),
            'phone.digits' => __('words.phone_digits_validation'),
            'phone.unique' => __('words.email_unique_validation'),
        ];
    }
}
