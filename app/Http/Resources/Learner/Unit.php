<?php

namespace App\Http\Resources\Learner;

use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Facades\Auth;

class Unit extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request HTTP Request
     *
     * @return array
     */
    public function toArray($request)
    {
        $return =  [
            'id' => $this->id,
            'title' => $this->title,
            'course_id' => $this->course_id,
            'order' => $this->order,
        ];

        if ($this->course) {
            $this->course->units =[];
            $return['course'] = Course::make($this->course);
        }

        if ($this->lessons) {
            $return['lessons'] = LessonLight::collection($this->lessons)->all();
            $progress = Auth::user()->getProgress();
            $return['progress_next'] = $progress['next'];
        }

        return $return;
    }

}
