<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLearner extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'required|max:191|unique:learners,email,'.$this->get('id'),
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'dialling_code' => 'required|max:255',
            'phone_number' => 'required|max:255',
            'language' => 'required',
            'course' => 'required',
            'country' => 'required',
            'address1' => 'max:255',
            'address2' => 'max:255',
            'zip_code' => 'max:255',
            'town' => 'max:255'
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
            'email.unique'  => 'There is already a learner in the system with this email address.',
            'firstname.max' => 'The first name may not be greater than 45 characters.',
            'lastname.max' => 'The last name may not be greater than 45 characters.'
        ];
    }
}
