<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;
use App\Http\Resources\Role as RoleResource;
use Illuminate\Support\Str;

class User extends Resource
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
            'name' => $this->getFullNameAttribute(),
            'email' => $this->email,
            'status' => Str::ucfirst(strtolower($this->status)),
            'active' => [
                'value' => $this->is_active,
                'name' => $this->getStatusName(),
            ],
            'role' => RoleResource::make($this->role),
            'created_at' => (($this->created_at) ? $this->created_at->format('Y-m-d H:i:s') : ''),
            'updated_at' => (($this->updated_at) ? $this->updated_at->format('Y-m-d H:i:s') : ''),
            'show_sent_label' => false
        ];
    }

}
