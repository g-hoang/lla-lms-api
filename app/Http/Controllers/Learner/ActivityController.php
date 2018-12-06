<?php

namespace App\Http\Controllers\Learner;

use App\Events\ActivityResponse;
use App\Events\Progress;
use App\Events\Track;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Learner\ActivityRequest;
use App\Models\Activity;
use App\Models\ActivityResponses;
use App\Models\LearnerUnits;
use App\Http\Resources\Learner\Activity as ActivityResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ActivityController extends ApiController
{
    protected $gate = 'learner';

    public function __construct()
    {
        Auth::shouldUse('learner');
    }

    /**
     * get listing of components.
     *
     * @param ActivityRequest $request Http Request
     *
     * @param $activity_id
     * @return mixed
     */
    public function index(ActivityRequest $request, $activity_id)
    {
        $progress = ['current' => 0, 'total' => 0, 'next_activity' => null, 'next_activity_title' => ''];
        $prev_activity = null;
        try {
            $activity = Activity::with(['lesson', 'lesson.unit', 'components' => function ($q) {
                $q->orderBy('order', 'ASC');
            }])->where('is_disabled', false)
                ->findOrFail($activity_id);

            $activities = Activity::where(['lesson_id' => $activity->lesson_id])
                ->where('is_disabled', false)
                ->orderBy('order', 'ASC')
                ->get()
                ->toArray();

            foreach ($activities as $i => $act) {
                if ($act['id'] == $activity->id) {
                    $progress['current'] = $i+1;
                    $progress['next_activity'] = (isset($activities[$i+1]) ? $activities[$i+1]['id'] : null);
                    $progress['next_activity_title'] = (isset($activities[$i+1]) ? $activities[$i+1]['order'] . '-' . $activities[$i+1]['title'] : null);

                    $prev_activity = (isset($activities[$i-1]) ? $activities[$i-1]['id'] : null);
                }
            }

            $progress['total'] = count($activities);
        } catch (\Exception $e) {
            return $this->respondNotFound('Activity Not Found');
        }

        // Skip restrictions for special learner (id = 1)
        if (Auth::user()->id != 1 && $prev_activity && Auth::user()->getActivityStatus($prev_activity) != 'COMPLETED') {
            $progress = Auth::user()->getProgress();
            return $this->setStatusCode(423)
                ->respond([
                    'error' => 'Activity Locked. Complete previous activities first.',
                    'data' => ['progress_next' => $progress['next']],
                    'status_code' => 423
                ]);
        }

        if (Auth::user()->id != 1) {
            $prev = Auth::user()->getProgress();
            if ($prev['prev'] &&
                $prev['prev']->unit_id != $activity->lesson->unit_id &&
                $activity->lesson->unit->order != 1
            ) {
                $isF2FCompleted = LearnerUnits::where('learner_id', Auth::user()->id)
                    ->where('unit_id', $prev['prev']->unit_id)
                    ->exists();

                if (!$isF2FCompleted) {
                    return $this->setStatusCode(423)
                        ->respond([
                            'error' => 'Activity Locked. You must attend a F2F class.',
                            'data' => ['progress_next' => $prev['next']],
                            'status_code' => 423
                        ]);
                }
            }
        }


        $response = new ActivityResource($activity);
        $response = $response->toArray($request);
        $response['lesson'] = [
            'id' => $activity->lesson->id,
            'title' => $activity->lesson->title,
            'order' => $activity->lesson->order,
            'isEndOfUnitAssessment' => $activity->lesson->lesson_type_id == 4 ? true : false,
            'status' => auth()->user()->getLessonStatus($activity->lesson->id)
        ];
        $response['unit'] = ['id' => $activity->lesson->unit->id, 'title' => $activity->lesson->unit->title, 'order' => $activity->lesson->unit->order];
        $response['progress'] = $progress;

        $activity_status = auth()->user()->getActivityStatus($activity_id);
        $response['status'] = $activity_status;

        if ($response['lesson']['isEndOfUnitAssessment']) {
            $response['history'] = [];
            $response['previousActivityId'] = null;
            $response['nextActivityId'] = null;
        } else {
            $response['history'] = ActivityResponses::getHistory(auth()->user()->id, $activity);

            $activity_ids = [];
            try {
                $activities = Activity::where('is_disabled', false)
                    ->where('lesson_id', $activity->lesson->id)
                    ->orderBy('order', 'ASC')
                    ->get();

                foreach ($activities as $act) {
                    $activity_ids[$act->order] = $act->id;
                }
            } catch (\Exception $e) {
                // It's ok to ignore?
            }

            if ($response['order'] == 1) {
                if ($activity_status !== 'COMPLETED') {
                    $response['previousActivityId'] = null;
                    $response['nextActivityId'] = null;
                } else {
                    $response['previousActivityId'] = null;
                    if (isset($activity_ids[$response['order'] + 1]) && $activity_ids[$response['order'] + 1]) {
                        $response['nextActivityId'] = $activity_ids[$response['order'] + 1];
                    } else {
                        $response['nextActivityId'] = null;
                    }
                }
            } else {
                if ($activity_status !== 'COMPLETED') {
                    if (isset($activity_ids[$response['order'] - 1]) && $activity_ids[$response['order'] - 1]) {
                        $response['previousActivityId'] = $activity_ids[$response['order'] - 1];
                    } else {
                        $response['previousActivityId'] = null;
                    }
                    $response['nextActivityId'] = null;
                } else {
                    if (isset($activity_ids[$response['order'] - 1]) && $activity_ids[$response['order'] - 1]) {
                        $response['previousActivityId'] = $activity_ids[$response['order'] - 1];
                    } else {
                        $response['previousActivityId'] = null;
                    }
                    if (isset($activity_ids[$response['order'] + 1]) && $activity_ids[$response['order'] + 1]) {
                        $response['nextActivityId'] = $activity_ids[$response['order'] + 1];
                    } else {
                        $response['nextActivityId'] = null;
                    }
                }
            }
        }

        event(new Progress($activity));

        return $response;
    }

    public function check(ActivityRequest $request, $activity_id)
    {
        try {
            $activity = Activity::with(['components' => function ($q) {
                $q->orderBy('order', 'ASC');
            }])->findOrFail($activity_id);
        } catch (\Exception $e) {
            return $this->respondNotFound('Activity Not Found');
        }

        $response = [];
        $response['activity_id'] = $activity_id;
        $response['is_correct'] = true;

        $data = $request->all();
        $components = $activity->components->toArray();


        $no_of_scorable_components = 0;
        $no_of_answers = 0;
        $correct_answers_count = 0;

        foreach ($components as $index => $component) {
            if ($component['component_type'] == 'MCQ') {
                $no_of_scorable_components++;
            } elseif ($component['component_type'] == 'GAP_FILL') {
                //
            } else {
                if ($component['component_type'] == 'TEXT_INPUT') {
                    $answer = ($activity->components[$index]) ? $activity->components[$index]->answer() : null;
                    $data[$index]['model_answer'] = isset($answer['model_answer']) && $answer['model_answer'] ? $answer['model_answer'] : null;
                }
                continue;
            }

            foreach ($data as $i => $item) {
                if ($item['id'] == $component['id']) {
                    if ($component['component_type'] == 'MCQ') {
                        $no_of_answers++;

                        if (!isset($component['data']['options'])) {
                            $data[$i]['is_correct'] = false;
                            continue;
                        }

                        $no_of_correct_answers = 0;
                        $has_incorrect_answer = false;

                        foreach ($component['data']['options'] as $option) {
                            if ($option['is_correct'] == true) {
                                $no_of_correct_answers++;
                            }
                            foreach ($item['answers'] as $j => $answer) {
                                if ($answer['title'] == $option['title']) {
                                    if ($option['is_correct'] == true) {
                                        $data[$i]['answers'][$j]['is_correct'] = true;
                                    } else {
                                        $data[$i]['answers'][$j]['is_correct'] = false;
                                        $has_incorrect_answer = true;
                                    }
                                }
                            }
                        }

                        if ($no_of_correct_answers != count($item['answers']) || $has_incorrect_answer) {
                            $data[$i]['is_correct'] = false;
                            $response['is_correct'] = false;
                            $response['message'] = 'One or more incorrect answers';
                        } else {
                            $correct_answers_count++;
                            $data[$i]['is_correct'] = true;
                        }
                    } elseif ($component['component_type'] == 'GAP_FILL') {
                        $no_of_scorable_components += sizeof($component['data']['answers']);

                        $return = $this->checkGapFillComponent($component, $data, $i);

                        $data = $return['data'];

                        $data[$index]['is_correct'] = $return['is_correct'];

                        if (!$return['is_correct']) {
                            $response['is_correct'] = false;
                        }

                        $correct_answers_count += $return['correct_answers_count'];

                        $no_of_answers += $return['correct_answers_count']; //isset($data[$i]['answers']) ? sizeof($data[$i]['answers']) : 0;
                    }
                }
            }

        }

        if ($no_of_scorable_components != $no_of_answers) {
            $response['is_correct'] = false;
            $response['message'] = 'Answers required for all ' . $no_of_scorable_components . ' components';
        }

        $response['components'] = $data;

        $progress = ['current' => 0, 'total' => 0, 'next_activity' => null, 'next_activity_title' => ''];
        if (true) { //$response['is_correct'] == true
            $activities = Activity::where(['lesson_id' => $activity->lesson_id])
                ->orderBy('order', 'ASC')
                ->get()
                ->toArray();

            foreach ($activities as $i => $act) {
                if ($act['id'] == $activity->id) {
                    $progress['current'] = $i+1;
                    $progress['next_activity'] = (isset($activities[$i+1]) ? $activities[$i+1]['id'] : null);
                    $progress['next_activity_title'] = (isset($activities[$i+1]) ? $activities[$i+1]['order'] . '-' . $activities[$i+1]['title'] : null);
                }
            }
            $progress['total'] = count($activities);
        }

        $progress['correct_answers'] = $correct_answers_count;
        $progress['total_scorable_components'] = $no_of_scorable_components;
        $response['progress'] = $progress;

        event(new Track($activity_id, 'UPDATE_SCORE', ['correct' => $correct_answers_count, 'wrong' => $no_of_scorable_components - $correct_answers_count]));

        event(new ActivityResponse($activity, $response['components']));

        return $response;
    }

    /**
     * Show Answers
     *
     * @param $activity_id
     * @return mixed
     */
    public function answers($activity_id)
    {
        try {
            $activity = Activity::with(['components' => function ($q) {
                $q->orderBy('order', 'ASC');
            }])->findOrFail($activity_id);
        } catch (\Exception $e) {
            return $this->respondNotFound('Activity Not Found');
        }

        $data = [];

        foreach ($activity->components as $component) {
            if ($answer = $component->answer()) {
                $data[] = $answer;
            }
        }

        return $this->respond($data);
    }

    /**
     * @param $component
     * @param $data
     * @param $i
     * @return array
     */
    private function checkGapFillComponent($component, $data, $i)
    {
        $all_correct = true;

        $correct_answers_count = 0;

        foreach ($component['data']['answers'] as $index => $answers) {
            $given_answer = isset($data[$i]['answers']) && isset($data[$i]['answers'][$index]['value'])
                ? Str::lower(trim($data[$i]['answers'][$index]['value']))
                : null ;

            $answers = array_map(function ($answer) {
                return Str::lower(trim($answer));
            }, $answers);

            if ($answer_is_correct = in_array($given_answer, $answers)) {
                $correct_answers_count++;
            };

            $data[$i]['answers'][$index]['is_correct'] = $answer_is_correct;

            if (!$answer_is_correct) {
                $all_correct = false;
            }
        }

        return [
            'data' => $data,
            'is_correct' => $all_correct,
            'correct_answers_count' => $correct_answers_count
        ];
    }
}
