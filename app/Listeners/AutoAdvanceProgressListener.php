<?php

namespace App\Listeners;

use App\Events\AutoAdvanceProgress;
use App\Events\Track;
use App\Models\Learner;
use App\Models\Progress;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AutoAdvanceProgressListener
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
     * @param AutoAdvanceProgress $event
     * @return void
     * @throws \Exception
     */
    public function handle(AutoAdvanceProgress $event)
    {
        if (!env('AUTO_ADVANCE', false)) {
            return;
        }

        $learner = $event->learner;
        $lesson_id = $event->lesson;

        $learner = Learner::find($learner->id);

        if ($learner->assignedCourse()->id != 2) {
            return;
        }

        while (true) {
            $progress = Progress::getProgress($learner->id, $learner->assignedCourse()->id);

            if ($progress['next']->lesson_id == $lesson_id) {
                break;
            }

            event(new Track($progress['next']->activity_id, 'COMPLETE', null, $learner));
        }
    }
}
