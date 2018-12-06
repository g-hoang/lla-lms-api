<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Database\Query\Builder;

class Lesson extends Model
{
    protected $fillable = ['title', 'unit_id', 'lesson_type_id', 'language_focus', 'order', 'is_optional', 'is_disabled'];

    protected $casts = [
        'is_optional' => 'boolean'
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
                $query->where("unit_id", $search->get('unit_id'));

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
     * Lesson Type
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function lessonType()
    {
        return $this->belongsTo(LessonType::class);
    }

    /**
     * Units
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * All Activities
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function allActivities()
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Activities
     *
     * @return Lesson
     */
    public function activities()
    {
        return $this->enabledActivities();
    }

    /**
     * Enabled Activities
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function enabledActivities()
    {
        return $this->hasMany(Activity::class)->where('is_disabled', false);
    }

    /**
     * Disabled Activities
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function disabledActivities()
    {
        return $this->hasMany(Activity::class)->where('is_disabled', true);
    }

    /**
     * Next order index
     *
     * @return int|mixed
     */
    public function nextActivityOrder()
    {
        if ($index = $this->enabledActivities()->max('order')) {
            return ++$index;
        }
        return 1;
    }

    public function isTheLastLessonOfAnUnit()
    {
        return $this->order === $this->unit->lessons()->max('order');
    }
}
