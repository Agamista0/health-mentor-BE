<?php

namespace App\Http\Requests;
use Illuminate\Validation\Rule;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $id_validation = '';
        if(session('appKey') == 531)
            $id_validation = 'max:14';
        return [
            'name' => 'required',
            'ID_number' => $id_validation,
            // 'name_ar' => 'required',
            // 'birth' => 'required',
            // 'nickName' => 'required',
            // 'type' => 'required',
            // 'email' => 'required|email',
            'phone' => 'required',
            // 'userName' => 'required',
            'division_id' => 'required_if:type,doctor',
            'about' => 'required_if:type,doctor',
        ];
    }
}
