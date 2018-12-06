<?php

namespace App\Http\Controllers;

use App\Models\LessonType;
use Illuminate\Http\Request;
use App\Http\Resources\LessonType as LessonTypeCollection;


class LessonTypeController extends ApiController
{
    /**
     * Display a listing of lessons by unit.
     *
     * @param Request $request Http Request
     *
     * @return mixed
     */
    public function index(Request $request)
    {
        // $this->authorize('lesson.list');

        $lesson_types = LessonType::all();

        return LessonTypeCollection::collection($lesson_types);
    }

    
}
