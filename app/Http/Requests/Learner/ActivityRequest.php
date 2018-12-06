<?php

namespace App\Http\Requests\Learner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class ActivityRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this->getMethod() == 'GET' || $this->getMethod() == 'POST') {
            $id = $this->route('id');
            $course = DB::select('SELECT
                units.course_id
                FROM
                activities
                JOIN lessons
                ON activities.lesson_id = lessons.id 
                JOIN units
                ON lessons.unit_id = units.id
                WHERE
                activities.id = ?', [$id]);

            if (!count($course)) {
                return false;
            }
            $course_id = $course[0]->course_id;

            // Special account can access any activity
            if (auth()->user()->id == 1) {
                return true;
            }

            $learner = auth()->user()->load('courses');

            if (!count($learner->courses)) {
                return false;
            }

            if ($learner->assignedCourse()->id != $course_id) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }
}
