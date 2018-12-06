<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;
use App\Http\Resources\LessonType as LessonTypeResource;

class Lesson extends Resource
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
            'is_optional' => $this->is_optional,
            'created_at' => (($this->created_at) ? $this->created_at->format('Y-m-d H:i:s'):''),
            'updated_at' => (($this->updated_at) ? $this->updated_at->format('Y-m-d H:i:s') : ''),
            'enabled_activities' => $this->whenLoaded('enabledActivities'),
            'disabled_activities' => $this->whenLoaded('disabledActivities'),
            'activities' => $this->whenLoaded('activities')
        ];

        return $return;
    }

}
