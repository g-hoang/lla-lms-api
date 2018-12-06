<?php

namespace App\Linkup\Observers;

use App\Events\UserLog;
use App\Models\Activity;

/**
 * Class ActivityObserver
 *
 * @package App\Linkup\Observers
 */
class ActivityObserver
{

    /**
     * Listen to creating
     *
     * @param Activity $activity
     * @return null
     */
    public function creating(Activity $activity)
    {
    }

    /**
     * Listen to the created event.
     *
     * @param Activity $activity
     * @return void
     */
    public function created(Activity $activity)
    {
        event(new Userlog($activity->id, 'ACTIVITY_CREATED', ""));
    }

    public function updated(Activity $activity)
    {
        event(new Userlog($activity->id, 'ACTIVITY_UPDATED', ""));
    }

    /**
     * Listen to the deleting event.
     *
     * @param Activity $activity
     * @return void
     */
    public function deleting(Activity $activity)
    {
        event(new Userlog($activity->id, 'ACTIVITY_DELETED', ""));
    }
}
