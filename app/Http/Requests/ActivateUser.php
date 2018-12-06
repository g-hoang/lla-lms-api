<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActivateUser extends FormRequest
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
        /*
         * Validate status and is_active along with the email
         */
        return [
            'email' => "required|string|email",
            'token' => 'required|string',
            'password' => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'
        ];
    }
}
