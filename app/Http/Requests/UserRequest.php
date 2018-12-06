<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // We use Gates for ACL
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ];

        switch ($this->method()) {
            case 'GET':
            case 'DELETE':
                return [];

            case 'POST':
                return $rules;

            case 'PUT':
            case 'PATCH':
                unset($rules['email']);
                unset($rules['password']);
                return $rules;

            default:
                break;
        }
    }
}
