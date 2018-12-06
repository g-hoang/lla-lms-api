<?php

namespace App\Linkup\Observers;

use App\Events\UserLog;
use App\Models\Lesson;

/**
 * Class LessonObserver
 *
 * @package App\Linkup\Observers
 */
class LessonObserver
{

    /**
     * Handle optional status for End Of Unit Assessments
     *
     * @param Lesson $lesson
     * @return null
     */
    public function creating(Lesson $lesson)
    {
        if ($lesson->lesson_type_id == 4) {
            $lesson->is_optional = false;
        }
    }

    /**
     * Listen to the created event.
     *
     * @param Lesson $lesson
     * @return void
     */
    public function created(Lesson $lesson)
    {
        event(new Userlog($lesson->id, 'LESSON_CREATED', ""));
    }

    /**
     * Handle optional status for End Of Unit Assessments
     *
     * @param Lesson $lesson
     */
    public function saving(Lesson $lesson)
    {
        if ($lesson->lesson_type_id == 4) {
            $lesson->is_optional = false;
        }
    }

    /**
     * @param Lesson $lesson
     */
    public function updated(Lesson $lesson)
    {
        event(new Userlog($lesson->id, 'LESSON_UPDATED', ""));
    }

    /**
     * Listen to the deleting event.
     *
     * @param Lesson $lesson
     * @return void
     */
    public function deleting(Lesson $lesson)
    {
        event(new Userlog($lesson->id, 'LESSON_DELETED', ""));
    }
}
