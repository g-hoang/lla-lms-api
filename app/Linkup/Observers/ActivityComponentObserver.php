<?php

namespace App\Linkup\Observers;

use App\Events\UserLog;
use App\Models\ActivityComponent;

/**
 * Class ActivityObserver
 *
 * @package App\Linkup\Observers
 */
class ActivityComponentObserver
{

    /**
     * Listen to creating
     *
     * @param ActivityComponent $component
     * @return null
     */
    public function creating(ActivityComponent $component)
    {
        $component->order = 1;

        $latest_order_index = ActivityComponent::where('activity_id', $component->activity_id)->max('order');

        if ($latest_order_index) {
            $component->order = $latest_order_index + 1;
        }
    }

    /**
     * Listen to the created event.
     *
     * @param ActivityComponent $activity
     * @return void
     */
    public function created(ActivityComponent $activity)
    {
        event(new Userlog($activity->id, 'COMPONENT_CREATED', ""));
    }

    public function updated(ActivityComponent $activity)
    {
        event(new Userlog($activity->id, 'COMPONENT_UPDATED', ""));
    }

    /**
     * Listen to the deleting event.
     *
     * @param ActivityComponent $activity
     * @return void
     */
    public function deleting(ActivityComponent $activity)
    {
        event(new Userlog($activity->id, 'COMPONENT_DELETED', ""));
    }
}
