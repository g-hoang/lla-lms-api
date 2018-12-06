<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;
use App\Http\Resources\Role as RoleResource;
use Illuminate\Support\Str;

class Learner extends Resource
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
        $course = $this->assignedCourse();

        $return = [
            'id' => $this->id,
            'first_name' => $this->firstname,
            'last_name'=> $this->lastname,
            'dialling_code'=> $this->dialingcode,
            'phone_number'=> $this->phone,
            'phone_number'=> $this->phone,
            'address1'=> $this->address1,
            'address2'=> $this->address2,
            'town'=> $this->town,
            'zip_code'=> $this->zip,
            'email' => $this->email,
            'country'=> $this->country_id,
            'language'=> $this->language_id,
            'status' => Str::ucfirst(strtolower($this->status)),
            'active' => [
                'value' => $this->is_active,
                'name' => $this->getStatusName(),
            ],
            'is_active' => $this->is_active,
            'course' => ($course) ? $course->id : null,
            'center' => $this->center_id
        ];

        if (isset($this->preview) && $this->preview) {
            $return['mode'] = 'preview';
        }

        if ($this->whenLoaded('center')) {
            $return['center'] = Center::make($this->whenLoaded('center'));
        }

        if ($this->whenLoaded('active')) {
            $return['active'] = [
                'value' => $this->is_active,
                'name' => $this->getStatusName(),
            ];
        }

        return $return;
    }

}
