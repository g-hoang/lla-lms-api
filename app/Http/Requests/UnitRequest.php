<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UnitRequest extends FormRequest
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
            'title' => [
                'required',
                'string',
                'max:255',
                'unique:units,title,'.$this->get('id').',id,course_id,'.$this->get('course_id')
            ],
            'course_id' => 'required|integer'
        ];

        switch ($this->method()) {
            case 'GET':
            case 'DELETE':
                return [];

            case 'POST':
                return $rules;

            case 'PUT':
            case 'PATCH':
                unset($rules['course_id']);
                return $rules;

            default:
                break;
        }
    }

    /**
     * Custom Messages
     *
     * @return array
     */
    public function messages()
    {
        return [
            'title.unique' => 'A unit with this name already exists in this course',
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
            'title' => 'unit name'
        ];
    }

}
