<?php

namespace App\Listeners;

use App\Events\ActivityResponse;
use App\Models\ActivityResponses;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ActivityResponseListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  ActivityResponse  $event
     * @return void
     */
    public function handle(ActivityResponse $event)
    {
        $learner_id = $event->learner->id;
        $activity_id = $event->activity->id;


        $data = [];
        foreach ($event->data as $comp) {
            if (in_array($comp['component_type'], ['MCQ', 'TEXT_INPUT', 'GAP_FILL'])) {
                $data[] = $comp;
            }
        }

        $data = json_encode($data);

        $ar = ActivityResponses::firstOrNew(
            ['learner_id' => $learner_id, 'activity_id' => $activity_id]
        );
        $ar->data = $data;
        $ar->save();
    }
}
