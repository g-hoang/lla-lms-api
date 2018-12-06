<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CourseRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:courses,name,'.$this->get('id'),
        ];

        switch ($this->method()) {
            case 'GET':
            case 'DELETE':
                return [];

            case 'POST':
                return $rules;

            case 'PUT':
            case 'PATCH':
                return $rules;

            default:
                break;
        }
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'Course name is required',
            'name.unique'  => 'A course with this name already exists',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'name' => 'course name'
        ];
    }
}
