<?php

namespace App\Listeners;

use App\Events\Progress;
use App\Events\Track;
use App\Models\Activity;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\ProgressHistory;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

class ProgressEventSubscriber
{
    protected $actor;

    function __construct()
    {
        $this->actor = auth()->user();
    }

    /**
     *
     * @param null $event Object
     *
     * @param null $record
     * @return void
     */
    public function onStartProgress($event, $record = null)
    {
        $learner = $event->learner;

        if ($learner->id == 1) { // SKIP for special user account
            return;
        }

        $activity = (is_object($event->activity) ? $event->activity : Activity::find($event->activity));

        if (!$record) {
            $record = \App\Models\Progress::getTrackingRecord($learner->id, $activity->id);
        }

        if (!$record->id) {
            $components = $activity->components->all();
            $lesson = ($activity->lesson ? $activity->lesson : Lesson::find($activity->lesson_id));
            $unit = ($lesson->unit ? $lesson->unit : Unit::find($lesson->unit_id));
            $course = ($unit->course ? $unit->course : Course::find($unit->course_id));

            $record->learner_name = $learner->FullName;
            $record->learner_email = $learner->email;
            $record->course_id = $course->id;
            $record->course_name = $course->name;
            $record->unit_id = $unit->id;
            $record->unit_index = $unit->order;
            $record->unit_title = $unit->title;
            $record->lesson_id = $lesson->id;
            $record->lesson_index = $lesson->order;
            $record->lesson_title = $lesson->title;
            $record->activity_id = $activity->id;
            $record->activity_index = $activity->order;
            $record->activity_title = $activity->title;
            $record->activity_focus = $activity->focus;
            $record->is_part_of_assessment = $lesson->lesson_type_id == 4 ? true : false;
            $record->is_optional = $activity->is_optional;

            $record->max_time = $activity->max_time;
            $record->expiry_time = ($activity->max_time ? Carbon::now()->addSeconds($activity->max_time + env('PROGRESS_EXPIRY_BUFFER', 60)) : Carbon::now()->addSeconds(env('PROGRESS_EXPIRY_TIME', 900)));
            $record->max_attempts = $activity->max_attempts;

            $record->attempts = 1;
            $record->start_time = Carbon::now();

            $record->scorable_components = 0;
            foreach ($components as $comp) {
                if ($comp->component_type == 'MCQ') {
                    $record->scorable_components += 1;
                } elseif ($comp->component_type == 'GAP_FILL') {
                    $record->scorable_components += sizeof($comp->data['answers']);
                }
            }

            $record->scorable_correct = 0;
            $record->scorable_wrong = 0;
        } else {
            $record->attempts += 1;
        }

        $record->save();
    }

    /**
     * @param $event
     * @throws \Exception
     */
    public function onTrackProgress($event)
    {
        $learner = $event->learner;

        if ($learner->id == 1) { // SKIP for special user account
            return;
        }

        $activity_id = $event->activity_id;
        $type = $event->type;
        $extra = $event->extra;

        $record = \App\Models\Progress::getTrackingRecord($learner->id, $activity_id);

        if (!$record->id) {
            $e = new Progress($activity_id, $learner);
            $this->onStartProgress($e, $record);
            $record = \App\Models\Progress::getTrackingRecord($learner->id, $activity_id);
        }

        switch ($type) {
            case 'ATTEMPT':
                $record->attempts += 1;
                break;
            case 'EXIT':
                $record->exit_event = 'EXIT';
                $record->end_time = Carbon::now();
                break;
            case 'SKIP':
                $record->exit_event = 'SKIPPED';
                $record->end_time = Carbon::now();
                break;
            case 'COMPLETE':
                $record->exit_event = 'COMPLETED';
                $record->end_time = Carbon::now();
                break;
            case 'TIME_EXCEED':
                $record->exit_event = 'MAX_TIME_EXCEEDED';
                $record->end_time = Carbon::now();
                break;
            case 'ATTEMPTS_EXCEED':
                $record->exit_event = 'MAX_ATTEMPTS_EXCEEDED';
                $record->end_time = Carbon::now();
                break;
            case 'UPDATE_SCORE':
                $record->scorable_correct = $extra['correct'];
                $record->scorable_wrong = $extra['wrong'];
                break;
            default:
                throw new \Exception('Unsupported Tracking Event!');
        }

        $record->save();

        $completed_statuses = ['ATTEMPTS_EXCEED', 'TIME_EXCEED', 'COMPLETE'];

        if (in_array($type, $completed_statuses)) {
            if ($activity = Activity::find($event->activity_id)) {
                (new ProgressHistory)->updateHistory($activity, $event->learner, $event->extra);
            }
        }
    }



    /**
     * Map listeners and events.
     *
     * @param null $events Object
     *
     * @return void
     */
    public function subscribe($events)
    {
        $events->listen(
            Progress::class,
            'App\Listeners\ProgressEventSubscriber@onStartProgress'
        );
        $events->listen(
            Track::class,
            'App\Listeners\ProgressEventSubscriber@onTrackProgress'
        );
    }

}
