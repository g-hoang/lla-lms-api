<?php

namespace App\Http\Controllers;

use App\Events\AutoAdvanceProgress;
use App\Http\Requests\StoreLearner;
use App\Http\Resources\CourseProgress;
use App\Http\Resources\Learners as LearnersResource;
use App\Http\Resources\Learner as LearnerResource;
use App\Linkup\Facades\Search;
use App\Models\Course;
use App\Models\Learner;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProgressController extends ApiController
{
    /**
     * Display learner progress data.
     *
     * @param $learner_id
     * @return array
     */
    public function progress($learner_id)
    {
        $learner = Learner::find($learner_id);
        $course = $learner->assignedCourse();

        $response['course'] = CourseProgress::make($course)
            ->additional(['learner' => $learner]);

        return $this->respond($response);
    }
}
