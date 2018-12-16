<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Facades\Storage;

class ActivityComponent extends Resource
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
        $s3 = Storage::disk('s3');
        $data = $this->data;
        $path_prefix = 'components/' . $this->id;

        if ($this->component_type == 'TEXT_OUTPUT' && isset($data['id']) && !empty($data['id'])) {
            $input = \App\Models\ActivityComponent::find($data['id']);
            if ($input && isset($input->data['title'])) {
                $data['title'] = $input->data['title'];
            }
        }

        if (isset($data['filename'])) {
            if ($this->component_type == 'AUDIO') {
                $url = $s3->temporaryUrl(
                    $path_prefix . "-" . $data['filename'],
                    now()->addMinutes(5)
                );
                $data['url'] = $url;
            }
			
			if ($this->component_type == 'VIDEO') {
                $url = $s3->temporaryUrl(
                    $path_prefix . "-" . $data['filename'],
                    now()->addMinutes(5)
                );
                $data['url'] = $url;
            }

            if ($this->component_type == 'IMAGE') {
                $url_small = $s3->temporaryUrl(
                    $path_prefix . "-small-" . $data['filename'],
                    now()->addMinutes(5)
                );
                $data['url_small'] = $url_small;

                $url_medium = $s3->temporaryUrl(
                    $path_prefix . "-medium-" . $data['filename'],
                    now()->addMinutes(5)
                );
                $data['url_medium'] = $url_medium;

                $url_full = $s3->temporaryUrl(
                    $path_prefix . "-full-" . $data['filename'],
                    now()->addMinutes(5)
                );
                $data['url_full'] = $url_full;
            }

        } elseif ($this->component_type == 'TEXT_INPUT') {
            $data['text'] = isset($data['text']) && $data['text'] != '' ? $data['text'] : false;

            if (isset($data['image'])) {
                $url_small = $s3->temporaryUrl(
                    $path_prefix . "-small-" . $data['image'],
                    now()->addMinutes(5)
                );
                $data['url_small'] = $url_small;

                $url_medium = $s3->temporaryUrl(
                    $path_prefix . "-medium-" . $data['image'],
                    now()->addMinutes(5)
                );
                $data['url_medium'] = $url_medium;

                $url_full = $s3->temporaryUrl(
                    $path_prefix . "-full-" . $data['image'],
                    now()->addMinutes(5)
                );
                $data['url_full'] = $url_full;
            }
        }

        $return = [
            'id' => $this->id,
            'type' => ucfirst(strtolower($this->component_type)),
            'component_type' => $this->component_type,
            'data' => $data,
            'activity_id' => $this->activity_id,
            'activity' => $this->whenLoaded('activity'),
            // 'related_activity' => $this->whenLoaded('related_activity')
        ];

        if ($this->component_type == 'TEXT_OUTPUT') {
            $return['related_activity'] = $this->related_activity;
        }

        return $return;
    }

}
