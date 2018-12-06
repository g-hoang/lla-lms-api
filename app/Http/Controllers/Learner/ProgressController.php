<?php

namespace App\Http\Controllers\Learner;

use App\Http\Controllers\ApiController;
use App\Http\Resources\CourseProgress;
use App\Models\Learner;
use Illuminate\Support\Facades\Auth;

class ProgressController extends ApiController
{
    protected $user = null;
    protected $gate = 'learner';

    public function __construct()
    {
        $this->user = new Learner();
        Auth::shouldUse('learner');
    }

    /**
     * Display learner progress data.
     *
     * @return array
     */
    public function progress()
    {
        $learner = Auth::user();
        $course = $learner->assignedCourse();

        $response['course'] = CourseProgress::make($course)
            ->additional(['learner' => $learner]);

        return $this->respond($response);
    }
}
