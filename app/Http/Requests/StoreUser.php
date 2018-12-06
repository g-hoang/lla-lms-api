<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreUser extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function authorize()
    {
        /* TODO : handle authentication */
        return Auth::user();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'max:191|unique:users,email,'.$this->get('id'),
            'firstname' => 'required|max:45',
            'lastname' => 'required|max:45'
        ];
    }

    /**
     * Validation Messages
     *
     * @return array
     */
    public function messages()
    {
        return [
            'email.required' => 'Email is required.',
            'email.unique'  => 'There is already an admin user in the system with this email address',
            'firstname.max' => 'The first name may not be greater than 45 characters.',
            'lastname.max' => 'The last name may not be greater than 45 characters.'
        ];
    }
}
