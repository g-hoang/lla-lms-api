<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Str;

class Learners extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($learner) {

                $current_course = $learner->assignedCourse();

                return [
                    'id' => $learner->id,
                    'name' => $learner->getFullNameAttribute(),
                    'email' => $learner->email,
                    'status' => Str::ucfirst(strtolower($learner->status)),
                    'active' => [
                        'value' => $learner->is_active,
                        'name' => $learner->getStatusName(),
                    ],
                    'course' => $current_course ? ['id' => $current_course['id'], 'name' => $current_course['name']] : null,
                    // 'course' => (($learner->courses && count($learner->courses)) ? ['id' => $learner->courses[0]['id'], 'name' => $learner->courses[0]['name']] : null),
                    // 'center' => Center::make($learner->center)
                    'show_sent_label' => false
                ];
            }),
            'links' => []
        ];
    }
}
