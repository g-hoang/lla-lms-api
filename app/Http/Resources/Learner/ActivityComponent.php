<?php

namespace App\Http\Resources\Learner;

use App\Models\ActivityResponses;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Facades\Auth;
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
            $data['value'] = ActivityResponses::getTextInputValue(Auth::user()->id, $data['id']);
        }

        if ($this->component_type == 'MCQ') {
            $options = [];
            $correct_count = 0;
            foreach ($data['options'] as $opt) {
                if ($opt['is_correct'] == true) {
                    $correct_count++;
                }
                unset($opt['is_correct']);
                $options[] = $opt;
            }
            $data['options'] = $options;
            if ($correct_count > 1) {
                $data['multi_choice'] = true;
            } else {
                $data['multi_choice'] = false;
            }
        } elseif ($this->component_type == 'GAP_FILL') {
            $data = $this->formatGapFillData();
        }

        if ($this->component_type == 'TEXT_INPUT') {
            $data = [
                'label' => !isset($data['label'])? : $data['label'],
                'id' => !isset($data['id'])? : $data['id'],
            ];

            $answer = $this->answer();
            $data['model_answer'] = isset($answer['model_answer']) && $answer['model_answer'] ? $answer['model_answer'] : null;

        }

        if (isset($data['filename'])) {
            if ($this->component_type == 'AUDIO') {
                $url = $s3->temporaryUrl(
                    $path_prefix . "-" . $data['filename'],
                    now()->addMinutes(15)
                );
                $data['url'] = $url;
            }

            if ($this->component_type == 'IMAGE') {
                $url_small = $s3->temporaryUrl(
                    $path_prefix . "-small-" . $data['filename'],
                    now()->addMinutes(15)
                );
                $data['url_small'] = $url_small;

                $url_medium = $s3->temporaryUrl(
                    $path_prefix . "-medium-" . $data['filename'],
                    now()->addMinutes(15)
                );
                $data['url_medium'] = $url_medium;

                $url_full = $s3->temporaryUrl(
                    $path_prefix . "-full-" . $data['filename'],
                    now()->addMinutes(15)
                );
                $data['url_full'] = $url_full;
            }
        }

        return [
            'id' => $this->id,
            'type' => ucfirst(strtolower($this->component_type)),
            'component_type' => $this->component_type,
            'data' => $data,
            'activity_id' => $this->activity_id,
            'order'=>$this->order,
            'is_valid' => (in_array($this->component_type, ['MCQ', 'TEXT_INPUT','GAP_FILL'])) ? false : true
        ];
    }

    /**
     * @return array
     */
    private function formatGapFillData()
    {
        $collection = [];

        $sections = isset($this->data['sections']) ? $this->data['sections'] : [];
        $sections_count = count($sections);

        $id = 0;

        foreach ($sections as $index => $section) {
            $collection[] = [
                'type' => 'text',
                'value' => $section
            ];
            if ($index < $sections_count - 1) {
                $collection[] = [
                    'type' => 'gap',
                    'value' => '',
                    'id' => $id++
                ];
            }

        }

        return [
            'gaps' => [],
            'question' => isset($this->data['question']) ? $this->data['question'] : '',
            'label' => $this->data['label'],
            'expected_answer_count' => (int) count($this->data['answers']),
            'options' => $collection
        ];
    }

}
