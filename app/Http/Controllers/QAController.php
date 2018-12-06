<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourseRequest;
use App\Models\Course;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use App\Linkup\Facades\Search;
use App\Http\Resources\Course as CourseResource;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class QAController extends ApiController
{
    /**
     * create courses for QA automation.
     * @param int $count
     * @return string
     */
    public function createDummyContent($type, $count = 50)
    {
        if (env('APP_ENV') != 'local' && env('APP_ENV') != 'development' && env('APP_ENV') != 'staging') {
            return "Are you crazy?";
        }

        if ($type == 'course') {
            $this->createCourses($count);
        }
        return "OK";
    }

    private function createCourses($count)
    {
        $recs = Course::count();

        for ($x=0; $x < $count - $recs; $x++) {
            factory(Course::class)->create();
        }
    }

    /**
     * clean created data.
     * @param $type
     * @return string
     */
    public function clean($type)
    {
        if (env('APP_ENV') != 'local' && env('APP_ENV') != 'development' && env('APP_ENV') != 'staging') {
            return "Are you crazy?";
        }

        if ($type == 'course') {
            Course::where('name', 'like', 'TEST Course %')->delete();
        }
        return 'OK';
    }
}
