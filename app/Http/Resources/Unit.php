<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

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
            'created_at' => (($this->created_at) ? $this->created_at->format('Y-m-d H:i:s'): ''),
            'updated_at' => (($this->updated_at) ? $this->updated_at->format('Y-m-d H:i:s') : ''),

        ];

        if ($this->course) {
            $return['course'] = Course::make($this->course);
        }

        if ($this->lessons) {
            $return['lessons'] = Lesson::collection($this->lessons);
        }

        return $return;
    }

}
