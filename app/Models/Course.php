<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Database\Query\Builder;

class Course extends Model
{
    protected $fillable = ['name'];

    /**
     * Course Search
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

                if (($keyword = $search->get('keyword')) !== false) {
                    $query->where("name", "LIKE", "%$keyword%");
                }
            }
        );

        $sortable_fields = ['name','created_at'];
        $sort_by = 'name';

        if (in_array($search->get('sortBy'), $sortable_fields)) {
            $sort_by = $search->get('sortBy');
        }
        $sort_order = [
            'ascending' => 'ASC',
            'descending' => 'DESC',
        ];

        $order = $sort_order[$search->get('order', 'ascending')];

        return $query->orderBy($sort_by, $order);
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }
}
