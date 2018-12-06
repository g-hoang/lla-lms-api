<?php
/**
 * Created by PhpStorm.
 * User: kenath
 * Date: 12/19/2017
 * Time: 10:14 AM
 */

namespace App\Linkup\Search;

use App\Models\Activity;
use App\Models\ActivityComponent;
use App\Models\Course;
use App\Models\Learner;
use App\Models\Lesson;
use App\Models\Unit;
use App\Models\User;

class Search
{
    public function users($search)
    {
        return User::search($search);
    }

    public function courses($search)
    {
        return Course::search($search);
    }

    public function units($search)
    {
        return Unit::search($search);
    }

    public function lessons($search)
    {
        return Lesson::search($search);
    }

    public function activities($search)
    {
        return Activity::search($search);
    }

    public function components($search)
    {
        return ActivityComponent::search($search);
    }

    public function learners($search)
    {
        return Learner::search($search);
    }

}