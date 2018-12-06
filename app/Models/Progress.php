<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Progress extends Model
{
    protected $table = 'learner_progress';

    protected $guarded = [];

    protected $casts = [
        'is_part_of_assessment' => 'boolean',
        'is_optional' => 'boolean',
    ];

    public static function getTrackingRecord($learner_id, $activity_id)
    {
        $found = false;
        $record = null;

        while (!$found) {
            $record = Progress::firstOrNew(
                ['learner_id' => $learner_id, 'activity_id' => $activity_id, 'exit_event' => 'INCOMPLETE']
            );

            $now = Carbon::now();

            if ($record->expiry_time && $record->expiry_time <= $now) {
                $record->exit_event = 'EXPIRED';
                $record->end_time = $now;
                $record->save();
                continue;
            }

//            if ($record->max_attempts && $record->max_attempts < $record->attempts) {
//                $record->exit_event = 'MAX_ATTEMPTS_EXCEEDED';
//                $record->end_time = $now;
//                $record->save();
//                continue;
//            }

            $found = true;
        }

        return $record;
    }

    /**
     * @param $learner_id
     * @param $course_id
     * @return array
     * @throws \Exception
     */
    public static function getProgress($learner_id, $course_id)
    {
        $prev = Progress::getPreviousActivity($learner_id, $course_id);

        if (!$prev) {
            $next = Progress::getFirstActivity($course_id);
        } else {
            try {
                $next = Progress::getNextActivity($prev->activity_id, $prev, $course_id);
            } catch (\Exception $e) {
                if ($e->getCode() == 1001) {
                    $course = Course::where('id', $course_id)
                        ->first();
                    $next = new \stdClass();
                    $next->course_id = $course_id;
                    $next->course_name = $course->name;
                    $next->courseCompleted = true;

                } else {
                    throw $e;
                }
            }
        }

        return ['prev' => $prev, 'next' => $next];
    }

    public static function getFirstActivity($course_id)
    {
        $ret = new \stdClass();
        $ret->course_id = $course_id;
        $ret->course_name = null;
        $ret->courseCompleted = false;
        $ret->unit_id = null;
        $ret->unit_index = null;
        $ret->lesson_id = null;
        $ret->lesson_index = null;
        $ret->activity_id = null;
        $ret->activity_index = null;
        $ret->isFirst = false;
        $ret->isNewLesson = false;
        $ret->lesson_skippable = false;
        $ret->is_assessment = false;

        if ($unit = Unit::where('course_id', $course_id)->orderBy('order', 'asc')->with('course')->first()) {
            $ret->course_name = $unit->course->name;
            $ret->courseCompleted = false;
            $ret->unit_id = $unit->id;
            $ret->unit_index = $unit->order;

            if ($lesson = Lesson::where('unit_id', $unit->id)->orderBy('order', 'asc')->first()) {
                $ret->lesson_id = $lesson->id;
                $ret->lesson_index = $lesson->order;

                if ($activity = Activity::where('lesson_id', $lesson->id)->where('is_disabled', false)
                    ->orderBy('order', 'asc')
                    ->first()) {
                    $ret->activity_id = $activity->id;
                    $ret->activity_index = $activity->order;
                    $ret->isFirst = true;
                    $ret->isNewLesson = true;
                    $ret->lesson_skippable = $lesson->is_optional;
                    $ret->is_assessment = ($lesson->lesson_type_id == 4 ? true : false);
                }

            }
        }

        if ($ret->lesson_skippable) {
            $ret->skip_to_lesson_details = self::getSkipToLessonDetails($activity);
        }

        return $ret;
    }

    public static function getPreviousActivity($learner_id, $course_id)
    {
        $record = DB::select(
            "SELECT
                course_id, unit_id, unit_index, lesson_id,lesson_index,activity_id,activity_index,exit_event,CONCAT((unit_index + 1000),(lesson_index+1000),(activity_index+1000)) as progress_index
            FROM
                learner_progress 
            WHERE
                exit_event NOT IN ( 'EXIT', 'EXPIRED', 'INCOMPLETE' ) 
                AND learner_id = ? 
                AND course_id = ? 
            ORDER BY
                progress_index DESC 
                LIMIT 1",
            [$learner_id, $course_id]
        );

        return count($record) ? $record[0] : null;
    }

    /**
     * @param $prev_activity_id
     * @param null $prev
     * @param null $course_id
     * @return \stdClass
     * @throws \Exception
     */
    public static function getNextActivity($prev_activity_id, $prev = null, $course_id = null)
    {
        $activity = Activity::find($prev_activity_id);
        if (!$activity->is_disabled) {
            $next = Activity::where('lesson_id', $activity->lesson_id)
                ->where('order', '>', $activity->order)
                ->where('is_disabled', false)
                ->orderBy('order', 'asc')
                ->first();
        } else {
            $next = Activity::where('lesson_id', $activity->lesson_id)
                ->where('order', '=', $prev->activity_index - 1)
                ->where('is_disabled', false)
                ->orderBy('order', 'asc')
                ->first();
            if ($next) {
                return Progress::getNextActivity($next->id);
            }
        }

        $isNewLesson = false;
        $isF2FCompleted = false;
        $is_f2f_not_complete = false;

        if (!$next) {
            $isNewLesson = true;
            $next = Progress::getNextLesson($activity->lesson_id);

            if (!$prev) {
                // $progress = Auth::user()->getProgress();
                // $prev = $progress['prev'];
                $course_id = $course_id ? $course_id: auth()->user()->assignedCourseId();
                $prev_activity = Progress::getPreviousActivity(auth()->user()->id, $course_id);
                $prev= new \stdClass();
                $prev->unit_id = $prev_activity ? $prev_activity->unit_id : null;
            }

            if ($prev->unit_id != $next->lesson->unit_id) {
                $isF2FCompleted = LearnerUnits::where('learner_id', Auth::user()->id)
                    ->where('unit_id', $prev->unit_id)
                    ->exists();

                $is_f2f_not_complete = !$isF2FCompleted;
            }
        }

        $ret = new \stdClass();
        $ret->course_id = $next->lesson->unit->course_id;
        $ret->course_name = $next->lesson->unit->course->name;
        $ret->courseCompleted = false;
        $ret->unit_id = $next->lesson->unit_id;
        $ret->unit_index = $next->lesson->unit->order;
        $ret->lesson_id = $next->lesson_id;
        $ret->lesson_index = $next->lesson->order;
        $ret->activity_id = $next->id;
        $ret->activity_index = $next->order;
        $ret->isFirst = false;
        $ret->isNewLesson = $isNewLesson;
        $ret->lesson_skippable = $next->lesson->is_optional;
        $ret->is_assessment = ($next->lesson->lesson_type_id == 4 ? true : false);
        $ret->is_f2f_not_complete = $is_f2f_not_complete;

        if ($ret->lesson_skippable) {
            $ret->skip_to_lesson_details = self::getSkipToLessonDetails($next);
        }

        return $ret;
    }

    /**
     * @param $prev_lesson_id
     * @return mixed
     * @throws \Exception
     */
    public static function getNextLesson($prev_lesson_id)
    {
        $lesson = Lesson::find($prev_lesson_id);
        $next = Lesson::where('unit_id', $lesson->unit_id)
            ->where('order', '>', $lesson->order)
            ->orderBy('order', 'asc')
            ->first();

        if ($next) {
            $activity = Activity::where('lesson_id', $next->id)
                ->where('is_disabled', false)
                ->orderBy('order', 'asc')
                ->first();

            while (!$activity) {
                // echo 'no activities for lesson ' . $next->id . "\n";
                $activity = Progress::getNextLesson($next->id);
            }

            return $activity;
        } else {
            return Progress::getNextUnit($lesson->unit_id);
        }
    }


    /**
     * @param $prev_unit_id
     * @return mixed
     * @throws \Exception
     */
    public static function getNextUnit($prev_unit_id)
    {
        $unit = Unit::find($prev_unit_id);
        $next = Unit::where('course_id', $unit->course_id)
            ->where('order', '>', $unit->order)
            ->orderBy('order', 'asc')
            ->first();

        if ($next) {
            $lesson = Lesson::where('unit_id', $next->id)
                ->orderBy('order', 'asc')
                ->first();

            if (!$lesson) {
                return self::getNextUnit($next->id);
            }

            $activity = Activity::where('lesson_id', $lesson->id)
                ->where('is_disabled', false)
                ->orderBy('order', 'asc')
                ->first();

            while (!$activity) {
//                echo 'no activities for lesson ' . $lesson->id . "\n";
                $activity = Progress::getNextLesson($lesson->id);
            }

            return $activity;
        } else {
            throw new \Exception('Course Completed', 1001);
        }
    }

    public static function loadLearnerActivityProgress($learner_id, $course_id)
    {
        $activities = [];
        $lessons =[];
        $records = DB::table('vProgress')
            ->where('learner_id', $learner_id)
            ->where('course_id', $course_id)
            ->get()
            ->all();
        foreach ($records as $r) {
            $activities[] = $r->activity_id;
            $lessons[$r->lesson_id] = false;
        }

        foreach ($lessons as $lid => $status) {
            $activity = Activity::where('lesson_id', $lid)
                ->where('is_disabled', false)
                ->orderBy('order', 'desc')
                ->first();
            if (in_array($activity->id, $activities)) {
                $lessons[$lid] = true;
            }
        }

        return ['activities' => $activities, 'lessons' => $lessons] ;
    }

    public static function getSkipToLessonDetails($next)
    {
        try {
            $next_lesson = Progress::getNextLesson($next->lesson_id);
        } catch (\Exception $e) {
            return null;
        }

        $skip_to_lesson_details = new \stdClass();
        $skip_to_lesson_details->unit_id = $next_lesson->lesson->unit_id;
        $skip_to_lesson_details->unit_index = $next_lesson->lesson->unit->order;
        $skip_to_lesson_details->unit_title = $next_lesson->lesson->unit->title;
        $skip_to_lesson_details->lesson_id = $next_lesson->lesson_id;
        $skip_to_lesson_details->lesson_index = $next_lesson->lesson->order;
        $skip_to_lesson_details->lesson_title = $next_lesson->lesson->title;
        $skip_to_lesson_details->lesson_type = $next_lesson->lesson->lessonType->name;
        $skip_to_lesson_details->activity_id = $next_lesson->id;
        $skip_to_lesson_details->activity_index = $next_lesson->order;
        $skip_to_lesson_details->isNewUnit = ($next_lesson->lesson->unit_id != $next->lesson->unit_id ? true : false);

        return $skip_to_lesson_details;
    }

    public static function getLearnerUnitScore($learner_id, $unit_id)
    {
        $summary = Progress::whereIn('id', function ($query) use ($learner_id, $unit_id) {
            $query->select(DB::raw('MAX(id) AS id'))
                ->from('learner_progress')
                ->where('exit_event', 'COMPLETED')
                ->where('learner_id', $learner_id)
                ->where('unit_id', $unit_id)
                ->where('is_part_of_assessment', 1)
                ->groupBy('activity_id');
        })
        ->select(DB::raw('sum(scorable_components) as total, sum(scorable_correct) as correct, sum(scorable_wrong) as wrong, max(created_at) as created_at'))
        ->first()
        ->toArray();

        return $summary;
    }

    public static function getLearnerUnitTimeSpent($learner_id, $unit_id)
    {
        $summary = Progress::where('learner_id', $learner_id)
            ->where('unit_id', $unit_id)
            ->whereNotIn('exit_event', ['INCOMPLETE', 'EXPIRED', 'SKIPPED'])
            ->select(DB::raw('sum(TIME_TO_SEC(TIMEDIFF(end_time, start_time))) as duration'))
            ->first()
            ->toArray();

        return $summary['duration'];
    }

    public static function getLearnerUnitLastAccess($learner_id, $unit_id)
    {
        $summary = Progress::where('learner_id', $learner_id)
            ->where('unit_id', $unit_id)
            ->select(DB::raw('max(start_time) as date'))
            ->first()
            ->toArray();

        return $summary['date'];
    }
}
