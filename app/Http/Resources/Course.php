<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class Course extends Resource
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
            'name' => $this->name,
            'is_active' => [
                'value' => $this->is_active,
                'name' => $this->getStatusName(),
            ],
            'created_at' => ($this->created_at ? $this->created_at->format('Y-m-d H:i:s') : ''),
            'updated_at' => (($this->updated_at) ? $this->updated_at->format('Y-m-d H:i:s') : ''),
            'units' => new UnitCollection($this->whenLoaded('units'))
        ];

        return $return;
    }

}
