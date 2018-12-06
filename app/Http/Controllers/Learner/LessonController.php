<?php

namespace App\Http\Controllers\Learner;

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LessonController extends ApiController
{
    protected $gate = 'learner';

    public function __construct()
    {
        Auth::shouldUse('learner');
    }

    /**
     * get listing of components.
     *
     * @param $lesson_id
     * @return mixed
     */
    public function progress($lesson_id)
    {
        $sql = "select sum(scorable_components) as scorable_components, sum(scorable_correct) as scorable_correct, sum(scorable_wrong) as scorable_wrong from learner_progress 
                 where id in (
                  SELECT   MAX(learner_progress.id) AS id 
                  FROM     learner_progress
                    JOIN activities a on activity_id = a.id and a.is_disabled <> 1
                    where exit_event not in ('EXIT', 'INCOMPLETE', 'EXPIRED')
                     and learner_progress.lesson_id = ? and learner_progress.learner_id = ?
                  GROUP BY activity_id
                )";

        $result = DB::select($sql, [$lesson_id, auth()->user()->id]);

        if (count($result)) {
            $result = $result[0];
        } else {
            $result = new \stdClass();
            $result->scorable_components = 0;
            $result->scorable_correct = 0;
            $result->scorable_wrong = 0;
        }
        $score = 0;

        if ($result->scorable_components) {
            $score = round(($result->scorable_correct/$result->scorable_components) * 100, 0, PHP_ROUND_HALF_UP);
        } else {
            $score = 100;
        }

        return [
            'scorable_total' => (int) $result->scorable_components,
            'scorable_correct' => (int) $result->scorable_correct,
            'scorable_incorrect' => (int) $result->scorable_wrong,
            'score' => $score
        ];
    }
}
