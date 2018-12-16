<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Factory as ValidationFactory;

class ActivityComponentRequest extends FormRequest
{
    public function __construct(ValidationFactory $validationFactory)
    {

        $validationFactory->extend(
            'component_data_label',
            function ($attribute, $value, $parameters) {
                return $this->component_data_label($value);
            },
            'Component title is missing.'
        );

        $validationFactory->extend(
            'component_data_value',
            function ($attribute, $value, $parameters) {
                return $this->component_data_value($value);
            },
            'Component value is missing.'
        );

        $validationFactory->extend(
            'component_data_question',
            function ($attribute, $value, $parameters) {
                return $this->component_data_question($value);
            },
            "Question can't be empty."
        );

        $validationFactory->extend(
            'component_data_options',
            function ($attribute, $value, $parameters) {
                return $this->component_data_options($value);
            },
            "Invalid options detected."
        );

        $validationFactory->extend(
            'component_data_tag',
            function ($attribute, $value, $parameters) {
                return $this->component_data_tag($value);
            },
            "Alt tag property missing."
        );

        $validationFactory->extend(
            'component_file_mp3',
            function ($attribute, $value, $parameters) {
                if (!empty($value->getClientOriginalExtension())
                    && ($value->getClientOriginalExtension() == 'mp3' || $value->getClientOriginalExtension() == 'MP3')
                ) {
                    return true;
                } else {
                    return false;
                }
            },
            "File type not supported."
        );
        
        $validationFactory->extend(
            'component_file_mp4',
            function ($attribute, $value, $parameters) {
                if (!empty($value->getClientOriginalExtension())
                    && ($value->getClientOriginalExtension() == 'mp4' || $value->getClientOriginalExtension() == 'MP4')
                ) {
                    return true;
                } else {
                    return false;
                }
            },
            "File type not supported."
        );

        $validationFactory->extend(
            'required_without_filename',
            function ($attribute, $value, $parameters) {
                return $this->required_without_filename($value,$attribute,$parameters);
            },
            "File is required."
        );

        $validationFactory->extend(
            'component_data_id',
            function ($attribute, $value, $parameters) {
                return $this->component_data_id($value);
            },
            'ID is missing.'
        );

        $validationFactory->extend(
            'component_has_a_gap_fill',
            function ($attribute, $value) {
                return $this->component_has_a_gap_fill($value);
            },
            "At least one gap fill is required"
        );

        $validationFactory->extend(
            'has_no_empty_gap_fill',
            function ($attribute, $value) {
                return $this->has_no_empty_gap_fill($value);
            },
            "Gap fill field at least require one answer."
        );

        $validationFactory->extend(
            'not_exceeding_limit_gap_fill',
            function ($attribute, $value) {
                return $this->not_exceeding_limit_gap_fill($value);
            },
            "More than 20 gap fills found."
        );

        $validationFactory->extend(
            'validate_markup_gap_fill',
            function ($attribute, $value) {
                return $this->validate_markup_gap_fill($value);
            },
            "Invalid content. Make sure you have correct number of opening & closing brackets."
        );

        $validationFactory->extend(
            'model_text',
            function ($attribute, $value) {
                return $this->model_text($value);
            },
            "Model answer or image required."
        );
    }
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // We use Gates for ACL
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'activity_id' => 'required|integer',
            'component_type' => 'required|in:IMAGE,AUDIO,TEXT_BLOCK,MCQ,TEXT_INPUT,TEXT_OUTPUT,GAP_FILL,VIDEO'
        ];

        switch ($this->get('component_type')) {
            case 'TEXT_BLOCK':
                $rules['data'] = $this->rulesForTextBlock();
                break;
            case 'MCQ':
                $rules['data'] = $this->rulesForMCQ();
                break;
            case 'IMAGE':
                $rules = $this->rulesForImage();
                break;
            case 'AUDIO':
                $rules['data'] = $this->rulesForAudio();
                if ($this->method() == 'PUT' || $this->method() == 'PATCH') {
                    $rules['audio'] ='required|component_file_mp3|max:5120|min:1';
                }
                break;
            case 'VIDEO':
                $rules['data'] = $this->rulesForVideo();
                if ($this->method() == 'PUT' || $this->method() == 'PATCH') {
                    $rules['video'] ='required|component_file_mp4|max:5120|min:1';
                }
                break;
            case 'TEXT_INPUT':
                $rules = $this->rulesForTextInput($rules);
                break;
            case 'TEXT_OUTPUT':
                $rules['data'] = $this->rulesForTextOutput();
                break;
            case 'GAP_FILL':
                $rules['data'] = $this->rulesForGapFill();
                break;
        }


        switch ($this->method()) {
            case 'GET':
            case 'DELETE':
                return [];

            case 'POST':
                return $rules;

            case 'PUT':
            case 'PATCH':
                unset($rules['activity_id']);
                return $rules;

            default:
                break;
        }
    }

    private function rulesForTextBlock()
    {
        return 'required|component_data_label|component_data_value';

    }

    private function rulesForMCQ()
    {
        if ($this->method() == 'PUT' || $this->method() == 'PATCH' || $this->method() == 'POST') {
            return 'required|component_data_label|component_data_options';
        }
    }

    private function rulesForImage()
    {
        $rules['data'] = 'required|component_data_label';
        $rules['file'] ='image|mimes:jpeg,png,jpg|max:2048';
        $d = json_decode($this->get('data'), true);

        if(!isset($d['filename'])){
            $rules['file'] .= '|required';
        }

        return $rules;
    }

    private function rulesForAudio()
    {
        return 'required|component_data_label';

    }
    
    private function rulesForVideo()
    {
        return 'required|component_data_label';

    }

    private function rulesForTextOutput()
    {
        if ($this->method() == 'POST') {
            return 'required|component_data_label';
        } elseif ($this->method() == 'PUT' || $this->method() == 'PATCH') {
            return 'required|component_data_label|component_data_id';
        }
    }

    private function rulesForTextInput($rules)
    {
        $rules['data'] = 'required|json|model_text';
        $rules['file'] ='image|mimes:jpeg,png,jpg|max:2048';

        $data = json_decode($this->post('data'), true);
        if(!isset($data['image']) && (!isset($data['text']) || !$data['text'])){
            $rules['file'] .= '|required';
        }

        return $rules;

    }

    private function rulesForGapFill()
    {
        return 'required|component_data_label|component_has_a_gap_fill|has_no_empty_gap_fill|not_exceeding_limit_gap_fill|validate_markup_gap_fill';
    }

    private function component_data_label($value)
    {
        if (!is_array($value)) {
            $value = json_decode($value, true);
        }
        return (isset($value['label']) && !empty($value['label']));
    }

    private function component_data_value($value)
    {
        return (isset($value['value']) && !empty($value['value']));
    }

    private function component_data_question($value)
    {
        return (isset($value['question']) && !empty($value['question']));
    }

    private function component_data_options($value)
    {
        if (!isset($value['options']) || empty($value['options'])) {
            return false;
        }

        $has_correct_answer = false;
        foreach ($value['options'] as $option) {
            if (!isset($option['title']) || empty($option['title']) || !isset($option['is_correct'])) {
                return false;
            }

            if ($option['is_correct'] == true) {
                $has_correct_answer = true;
            }
        }

        return $has_correct_answer || true;
    }

    private function component_data_tag($value)
    {
        if (!is_array($value)) {
            $value = json_decode($value, true);
        }
        return (isset($value['alt_tag']));
    }

    private function component_data_id($value)
    {
        return (isset($value['id']) && !empty($value['id']));
    }

    private function component_has_a_gap_fill()
    {
        $data = $this->post('data');

        if(!$data['value']){
            return false;
        }

        preg_match_all('#\[(.*?)\]#', $data['value'], $matching_elements);

        if(!count($matching_elements[0])){
            return false;
        }

        return true;
    }

    private function has_no_empty_gap_fill()
    {
        $data = $this->post('data');

        preg_match_all('#\[(.*?)\]#', $data['value'], $matching_elements);

        foreach($matching_elements[0] as $index => $match ){
            if($matching_elements[1][$index] == ""){
                return false;
            };
        }

        return true;
    }

    private function not_exceeding_limit_gap_fill()
    {
        $data = $this->post('data');

        preg_match_all('#\[(.*?)\]#', $data['value'], $matching_elements);

        return count($matching_elements[0]) <= 20;
    }

    private function validate_markup_gap_fill()
    {
        $data = $this->post('data');
        $markup = strip_tags(htmlspecialchars_decode($data['value']), '<br>');
        $markup = str_replace('<br>', "\r\n", $markup);

        if (substr_count($markup, "[") != substr_count($markup, "]")) {
            return false;
        }

        $opened = false;
        foreach (str_split($markup) as $char) {
            if ($char == '[' && !$opened) {
                $opened = true;
                continue;
            } elseif ($char == '[' && $opened) {
                return false;
            }
            if ($char == ']' && $opened) {
                $opened = false;
                continue;
            } elseif ($char == ']' && !$opened) {
                return false;
            }
        }

        if ($opened) {
            return false;
        }

        return true;
    }

    private function model_text($value)
    {
        $data = json_decode($value, true);

        $file = $this->file('file');

        if(!isset($data['text']) && !$data['text'] && !isset($data['image']) && !$file){
            return false;
        }

        return true;

    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'file.image' => 'File type not supported.',
            'file.mimes'  => 'File type not supported.',
            'file.max'  => 'File size too large.',
            'file.min'  => 'File size too small.',
            'audio.mimes'  => 'File type not supported.',
            'audio.max'  => 'File size too large.',
            'audio.min'  => 'File size too small.',
            'video.mimes'  => 'File type not supported.',
            'video.max'  => 'File size too large.',
            'video.min'  => 'File size too small.',
        ];
    }
}
