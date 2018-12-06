<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Storage;

class ActivityComponent extends Model
{
    protected $fillable = ['activity_id', 'component_type', 'order', 'data'];

    protected $casts = [
        'data' => 'array'
    ];

    /**
     * Search
     *
     * @param Builder $query  query
     * @param Request $search search
     *
     * @return mixed
     */
    public function scopeSearch($query, Request $search)
    {
        $query->where(
            function ($query) use ($search) {
                $query->where("activity_id", $search->get('activity_id'));
            }
        );

        $sortable_fields = ['order'];
        $sort_by = 'order';

        if (in_array($search->get('sortBy'), $sortable_fields)) {
            $sort_by = $search->get('sortBy');
        }

        return $query->orderBy($sort_by);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    /**
     * @return array
     */
    public function getRelatedActivityAttribute()
    {
        if ($this->data && isset($this->data['id'])) {
            $component = ActivityComponent::select('id', 'activity_id', 'order', 'data')
                ->with(['activity' => function ($q) {
                    $q->select('title', 'id', 'order');
                    $q->where('order', '<>', '0');
                }])->find($this->data['id']);

            return $component->activity;
        }

        return [];
    }

    /**
     * Copy Components
     *
     * @param $data
     * @return Model
     */
    public function copy($data)
    {
        $newComponent = $this->replicate();

        $last_index = ActivityComponent::where('activity_id', $data->activity_id)->max('order');

        $newComponent->save();

        if ($this->component_type == 'IMAGE' && isset($this->data['filename'])) {
            foreach (['medium', 'small', 'full'] as $size) {
                $content = Storage::disk('s3')->get('components/'.$this->id.'-'.$size.'-'.$this->data['filename']);

                Storage::disk('s3')->put('components/'.$newComponent->id.'-'.$size.'-'.$this->data['filename'], $content);
            }
        } elseif ($this->component_type == 'AUDIO' && isset($this->data['filename'])) {
            $content = Storage::disk('s3')->get('components/'.$this->id.'-'.$this->data['filename']);

            Storage::disk('s3')->put('components/'.$newComponent->id.'-'.$this->data['filename'], $content);
        }

        $newComponent->fill([
            'unit_id' => $data->unit_id,
            'lesson_id' => $data->lesson_id,
            'activity_id' => $data->activity_id,
            'index' => $last_index
        ]);

        $newComponent->save();

        $newComponent->load([
            'activity' => function ($q) {
                $q->select('id', 'lesson_id');
                $q->with(['lesson' => function ($query) {
                    $query->select('id', 'unit_id');
                    $query->with(['unit' => function ($unit) {
                        $unit->select('id', 'course_id');
                    }]);
                }]);
            }
        ]);

        return $newComponent;
    }

    /**
     * Move Components
     *
     * @param $data
     * @return Model
     */
    public function move($data)
    {
        $last_index = ActivityComponent::where('activity_id', $data->activity_id)->max('order');

        $this->activity->updateActivityOrderIndexes();

        $attributes = [
            'unit_id' => $data->unit_id,
            'lesson_id' => $data->lesson_id,
            'activity_id' => $data->activity_id,
            'order' => $last_index + 1
        ];

        $this->fill($attributes);

        $this->save();

        $this->activity->updateActivityOrderIndexes();

        $this->load([
            'activity' => function ($q) {
                $q->select('id', 'lesson_id');
                $q->with(['lesson' => function ($query) {
                    $query->select('id', 'unit_id');
                    $query->with(['unit' => function ($unit) {
                        $unit->select('id', 'course_id');
                    }]);
                }]);
            }
        ]);

        return $this;
    }


    /**
     * Show Answers
     *
     * @return array
     */
    public function answer()
    {
        if ($this->component_type == 'MCQ') {
            return [
                'id' => $this->id,
                'component_type' => $this->component_type,
                'options' => $this->data['options']
            ];

        } elseif ($this->component_type == 'GAP_FILL') {
            $answers = [];

            foreach ($this->data['answers'] as $i => $answer) {
                $answers[] = ['id' => $i, 'answer' => $answer];
            }

            return [
                'id' => $this->id,
                'component_type' => $this->component_type,
                'options' => $answers,
            ];

        } elseif ($this->component_type == 'TEXT_INPUT') {
            $model_answer = [];

            if (isset($this->data['image'])) {
                $s3 = Storage::disk('s3');

                $path_prefix = 'components/' . $this->id;

                $time = now()->addMinutes(15);

                $model_answer = [
                    'alt_tag' => isset($this->data['alt_tag']) ? $this->data['alt_tag'] : null,
                    'image' => isset($this->data['image']) ? $this->data['image'] : null,
                    'url_small' => $s3->temporaryUrl($path_prefix . "-small-" . $this->data['image'], $time),
                    'url_medium' => $s3->temporaryUrl($path_prefix . "-medium-" . $this->data['image'], $time),
                    'url_full' => $s3->temporaryUrl($path_prefix . "-full-" . $this->data['image'], $time)
                ];

            }

            $model_answer['text'] = isset($this->data['text']) ? $this->data['text'] : null;

            return [
                'id' => $this->id,
                'component_type' => $this->component_type,
                'options' => [],
                'model_answer' => $model_answer
            ];
        }
    }
}
