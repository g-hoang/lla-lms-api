<?php
/**
 * Created by PhpStorm.
 * User: kenath
 * Date: 12/28/2017
 * Time: 10:31 PM
 */

namespace App\Linkup\Observers;

use App\Models\Activity;
use App\Models\ActivityComponent;
use App\Models\Course;
use App\Models\Learner;
use App\Models\Lesson;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\ServiceProvider;

class ObserverServiceProvider extends ServiceProvider
{
    /**
     * Register any events.
     *
     * @return void
     */
    public function boot()
    {
        User::observe(UserObserver::class);
        Course::observe(CourseObserver::class);
        Unit::observe(UnitObserver::class);
        Lesson::observe(LessonObserver::class);
        Activity::observe(ActivityObserver::class);
        ActivityComponent::observe(ActivityComponentObserver::class);
        Learner::observe(LearnerObserver::class);
    }

    /**
     * Register events.
     *
     * @return void
     */
    public function register()
    {
    }
}
