<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Database\Query\Builder;

class Activity extends Model
{
    protected $fillable = ['title', 'lesson_id', 'instructions', 'focus', 'order', 'is_optional', 'max_attempts', 'max_time', 'auto_advance_timer', 'is_disabled',];

    protected $casts = [
        'is_optional' => 'boolean',
        'is_disabled' => 'boolean'
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
                $query->where("lesson_id", $search->get('lesson_id'));

                if ($keyword = $search->get('keyword')) {
                    $query->where("title", "LIKE", "%$keyword%");
                }
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
    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function components()
    {
        return $this->hasMany(ActivityComponent::class);
    }

    /**
     * Text Output components
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function textOutputs()
    {
        return $this->components()
            ->where('component_type', 'TEXT_OUTPUT')
            ->where('data', 'NOT REGEXP', '"id":""')
            //->whereRaw('`data` NOT RLIKE \'"id":"[[:<:]]?[[:>:]]"\'', "")
            ->with('activity');
    }

    /**
     * Text Input components
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function textInputs()
    {
        return $this->components()
            ->where('component_type', 'TEXT_INPUTS')
            ->where('data', 'NOT REGEXP', '"id":""')
            //->whereRaw('`data` NOT RLIKE \'"id":"[[:<:]]?[[:>:]]"\'', "")
            ->with('activity');
    }

    /**
     * Update activity
     *
     * @param array $data
     * @throws \Throwable
     */
    public function updateAndSync($data = [])
    {

        $this->fill($data);

        $original = $this->getOriginal();

        if ($original['is_disabled'] == false && $data['is_disabled'] == true) {
            if ($components = $this->textOutputs()->get()) {
                foreach ($components as $component) {
                    $data = $component->data;

                    $data['id'] = '';

                    $component->update(['data' => $data]);

                }

            }

            $this->order = 0;

            $this->saveOrFail();

            return $this->updateActivityOrderIndexes();

        } elseif ($original['is_disabled'] == true && $data['is_disabled'] == false) {
            $this->order = $this->lesson->nextActivityOrder();
        }

        $this->saveOrFail();
    }

    /**
     * Update activity order indexes
     */
    public function updateActivityOrderIndexes()
    {
        // $this->lesson->enabledActivities()->orderBy('order','= 0')->orderBy('order','asc')->get()
        if ($activities = $this->lesson->enabledActivities()->orderBy('order', 'asc')->get()) {
            $index = 1;

            foreach ($activities as $activity) {
                $activity->fill([
                    'order' => $index++
                ])->save();

            }

        };
    }


    public function isTheLastActivityOfALesson()
    {
        return $this->order === $this->lesson->enabledActivities()->max('order');
    }

}
