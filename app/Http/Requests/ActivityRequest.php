<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActivityRequest extends FormRequest
{
    protected $defaults = [
        'max_time' => 0,
        'instructions' => '',
    ];
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
        $this->sanitize();

        $this->replace(array_merge($this->defaults, $this->filteredInput($this->all())));

        $rules = [
            'title' => 'required|string|max:255',
            'instructions' => 'max:2000',
            'lesson_id' => 'required|integer',
            'max_time' => 'nullable|max:99999|numeric'
        ];

        switch ($this->method()) {
            case 'GET':
            case 'DELETE':
                return [];

            case 'POST':
                return $rules;

            case 'PUT':
            case 'PATCH':
                unset($rules['lesson_id']);
                return $rules;

            default:
                break;
        }
    }

    /**
     * Sanitize Input
     */
    public function sanitize()
    {
        $input = $this->all();

        $input['title'] = trim($input['title']);
        $input['max_time'] = isset($input['max_time']) ? (int)($input['max_time']) : 0 ;

        $this->replace($input);
    }

    /**
     * @param $data
     * @return array
     */
    public function filteredInput($data)
    {
        return array_filter( $data, function($field) {
            return $field!== null;
        });
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'title' => 'activity name',
            'instructions' => 'instruction text'
        ];
    }

}
