<?php

namespace App\Http\Controllers\Learner;

use App\Events\Track;
use App\Http\Controllers\ApiController;
use App\Models\Activity;
use App\Models\ActivityResponses;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Progress;
use App\Models\ProgressHistory;
use App\Models\Unit;
use App\Models\Learner;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use RuntimeException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use App\Http\Resources\Learner\Course as CourseResource;
use App\Http\Resources\Learner\Unit as UnitResource;
use App\Http\Resources\Learner\LessonActivitiesOnly as LessonResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Learner\Activity as ActivityResource;
use Illuminate\Support\Facades\Config;
use App\Http\Resources\Learner\ActivityComponent as ActivityComponentResource;
use Illuminate\Support\Facades\DB;

class Controller extends ApiController
{
    protected $user = null;
    protected $gate = 'learner';

    /**
     * AuthController constructor.
     */
    public function __construct()
    {
        $this->user = new Learner();
        Auth::shouldUse('learner');
    }

    /**
     * Display a listing of courses.
     *
     * @param Request $request Http Request
     *
     * @return mixed
     */
    public function index(Request $request, $id = null)
    {
        if ($id && auth()->user()->id == 1) {
            $course = Course::with(['units' => function ($q) {
                $q->orderBy('order', 'ASC');
            }])->find($id);
        } else {
            $course = auth()->user()->assignedCourse();
        }

        $this->clearEOUAProgress();

        if ($course) {
            return new CourseResource($course);
        }

        return $this->respondNotFound('Course Not Found');
    }

    /**
     * Unit with lessons list
     *
     * @param $unit_id
     * @return UnitResource|mixed
     */
    public function unit($unit_id)
    {

        $unit = Unit::with(['lessons' => function ($query) {
            $query->orderBy('order', 'ASC')
                ->with('lessonType');
        }])->find($unit_id);

        $this->clearEOUAProgress($unit->lessons);

        if ($unit) {
            return new UnitResource($unit);
        }

        return $this->respondNotFound('Unit Not Found');
    }

    /**
     * Lesson details along with the activities
     *
     * @param $lesson_id
     * @return LessonResource|mixed
     */
    public function lesson($lesson_id)
    {
        $progress = Auth::user()->getProgress();
        try {
            $lesson = Lesson::with(['activities' => function ($q) {
                $q->where('is_disabled', false)->orderBy('order', 'ASC');
            }])->findOrFail($lesson_id);
        } catch (\Exception $e) {
            return $this->respondNotFound('Lesson Not Found');
        }

        if (Auth::user()->id != 1 && !$progress['next']->courseCompleted && $progress['next']->lesson_id != $lesson_id && Auth::user()->getLessonStatus($lesson_id) == 'PENDING') {
            if ($lesson->order != 1) {
                return $this->setStatusCode(423)
                    ->respond([
                        'error' => 'Lesson Locked. Complete previous Lesson first.',
                        'data' => ['progress_next' => $progress['next']],
                        'status_code' => 423
                    ]);
            }
        }

        if ($lesson->lesson_type_id == Config::get('enums.lesson_type.EndOfUnitAssessment')) {
            $lesson->first_activity = $lesson->activities->first();
            $lesson->first_activity->components = null;
            $lesson->activities = null;
            return $this->setStatusCode(423)
                ->respond([
                    'error' => 'Activity listing not allowed.',
                    'data' => new LessonResource($lesson),
                    'status_code' => 423
                ]);
        }

        if ($lesson) {
            return new LessonResource($lesson);
        }
    }

    public function skipLesson($lesson_id)
    {
        try {
            $lesson = Lesson::with(['activities' => function ($q) {
                $q->where('is_disabled', false)->orderBy('order', 'ASC');
            }])->findOrFail($lesson_id);
        } catch (\Exception $e) {
            return $this->respondNotFound('Lesson Not Found');
        }

        if (!$lesson->is_optional) {
            return $this->setStatusCode(401)
                ->respond([
                    'error' => 'Lesson is not skippable.',
                    'status_code' => 401
                ]);
        }

        $last_activity_id = null;
        foreach ($lesson->activities as $act) {
            $last_activity_id = $act->id;
            event(new Track($act->id, 'SKIP'));
        }

        $return['progress_next'] = Progress::getNextActivity($last_activity_id);

        return $return;
    }

    public function clearProgressData()
    {
        if (env("APP_ENV") != 'PRODUCTION') {
            $user_id = auth()->user()->id;

            Progress::where('learner_id', $user_id)->delete();

            ProgressHistory::where('learner_id', $user_id)->delete();

            ActivityResponses::where('learner_id', $user_id)->delete();

            Bugsnag::notifyException(new RuntimeException("Test error"));

            return "OK";
        }

        return "Are you mad?";
    }

    private function clearEOUAProgress()
    {
        $changed = false;
        $completed_items = Progress::loadLearnerActivityProgress(Auth::user()->id, Auth::user()->courses[0]['id']);
        foreach ($completed_items['lessons'] as $lesson_id => $status) {
            if (!$status) {
                $lesson = Lesson::find($lesson_id);
                if ($lesson->lesson_type_id == 4) {
                    DB::table('learner_progress')
                        ->where('learner_id', Auth::user()->id)
                        ->where('lesson_id', '=', $lesson->id)
                        ->delete();
                    $changed = true;
                }
            }
        }

        if ($changed) {
            Auth::user()->getProgress(true);
        }
    }
}
