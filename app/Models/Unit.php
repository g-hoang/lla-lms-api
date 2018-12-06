<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Database\Query\Builder;

class Unit extends Model
{
    protected $fillable = ['title', 'course_id'];

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
                $query->where("course_id", $search->get('course_id'));
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
     * Course
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Lessons
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    /**
     * Check whether the unit is the last of this course
     *
     * @return bool
     */
    public function isTheLastUnitOfACourse()
    {
        return $this->order === $this->course->units()->max('order');
    }
}
