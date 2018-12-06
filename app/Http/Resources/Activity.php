<?php

namespace App\Http\Resources;

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

        return [
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
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => (($this->updated_at) ? $this->updated_at->format('Y-m-d H:i:s') : ''),
            'is_disabled' => $this->is_disabled,
            'lesson' => Lesson::make($this->whenLoaded('lesson')),
            'components' => ActivityComponent::collection($this->whenLoaded('components')),
            // 'text_outputs' => $this->whenLoaded('textOutputs')
        ];
    }

}
