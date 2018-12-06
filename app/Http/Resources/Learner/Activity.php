<?php

namespace App\Http\Resources\Learner;

use Illuminate\Http\Resources\Json\Resource;

class Activity extends Resource
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
            'lesson_id' => $this->lesson_id,
            'instructions' => $this->instructions,
            'focus' => $this->focus,
            'order' => $this->order,
            'is_optional' => $this->is_optional,
            'max_attempts' => $this->max_attempts,
            'max_time' => $this->max_time,
            'auto_advance_timer' => $this->auto_advance_timer,
        ];


        if ($this->components) {
            $return['components'] = ActivityComponent::collection($this->components);
        }

        return $return;
    }

}
