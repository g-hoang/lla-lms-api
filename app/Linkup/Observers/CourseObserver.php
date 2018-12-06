<?php

namespace App\Linkup\Observers;

use App\Events\UserLog;
use App\Models\Course;

/**
 * Class CourseObserver
 *
 * @package App\Linkup\Observers
 */
class CourseObserver
{

    /**
     * Listen to creating
     *
     * @param Course $course
     * @return null
     */
    public function creating(Course $course)
    {
    }

    /**
     * Listen to the created event.
     *
     * @param Course $course
     * @return void
     */
    public function created(Course $course)
    {
        event(new Userlog($course->id, 'COURSE_CREATED', ""));
    }

    public function updated(Course $course)
    {
        event(new Userlog($course->id, 'COURSE_UPDATED', ""));
    }

    /**
     * Listen to the deleting event.
     *
     * @param Course $course
     * @return void
     */
    public function deleting(Course $course)
    {
        event(new Userlog($course->id, 'COURSE_DELETED', ""));
    }
}
