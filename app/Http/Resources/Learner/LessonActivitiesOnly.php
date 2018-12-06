<?php

namespace App\Http\Resources\Learner;

use Illuminate\Http\Resources\Json\Resource;
use App\Http\Resources\LessonType as LessonTypeResource;
use Illuminate\Support\Facades\Auth;

class LessonActivitiesOnly extends Resource
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
        $return  = [
            'id' => $this->id,
            'title' => $this->title,
            'unit_id' => $this->unit_id,
            'unit' => ['id' => $this->unit_id,'title' => $this->unit->title, 'order' => $this->unit->order],
            'course' => ['id' => $this->unit->course->id, 'name' => $this->unit->course->name],
            'lesson_type_id' => $this->lesson_type_id,
            'lesson_type' => LessonTypeResource::make($this->lessonType),
            'language_focus' => $this->language_focus,
            'order' => $this->order,
            'is_optional' => $this->is_optional
        ];

        if ($this->activities) {
            $progress = Auth::user()->getProgress();

            $return['activities'] = ActivityLight::collection($this->activities)->all();

            $return['progress_next'] = $progress['next'];
        }

        if ($this->first_activity) {
            $return['first_activity'] = ActivityLight::make($this->first_activity);
        }

        return $return;
    }
}
