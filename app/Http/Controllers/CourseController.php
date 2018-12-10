<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourseRequest;
use App\Models\Course;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use App\Linkup\Facades\Search;
use App\Http\Resources\Course as CourseResource;
use Illuminate\Support\Facades\Auth;

class CourseController extends ApiController
{
    /**
     * Display a listing of courses.
     *
     * @param Request $request Http Request
     *
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request)
    {
        $this->authorize('course.list');

        $course = Search::courses($request);

        if (!$request->get('list')) {
            $course->with('units');
        }

        if ($request->get('showAll')) {
            return CourseResource::collection($course->get());
        }

        if($size = $request->get('pageSize')){
            return CourseResource::collection($course->paginate((int)$size));
        };

        return CourseResource::collection($course->get());
    }


    /**
     * Store a newly created course.
     *
     * @param CourseRequest $request
     *
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(CourseRequest $request)
    {
        $this->authorize('course.create');

        $course = Course::create(
            [
                'name' => $request->name,
            ]
        );

        $msg = "Course created";
        return $this->respondCreated($msg, new CourseResource($course));
    }

    /**
     * Show course
     *
     * @param $id
     *
     * @return CourseResource|mixed
     * @throws AuthenticationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show($id)
    {
        $this->authorize('course.view');

        if ($course = Course::find($id)) {
            if (Auth::user()->cant('view', $course)) {
                throw new AuthenticationException();
            }

            return new CourseResource($course);
        }

        return $this->respondNotFound('Course Not Found');
    }


    /**
     * Update the specified resource in storage.
     *
     * @param CourseRequest $request
     * @param Course $course
     *
     * @return mixed
     * @throws AuthenticationException
     * @throws \Throwable
     */
    public function update(CourseRequest $request, Course $course)
    {
        $this->authorize('course.update');

        if (Auth::user()->cant('update', $course)) {
            throw new AuthenticationException();
        }
        $data = $request->only(['name']);
        $course->fill($data);
        $course->saveOrFail();
        return $this->respondSuccess('Course updated', CourseResource::make($course));
    }
    
    /**
     * Change user is_active state
     *
     * @param int     $id      Id
     * @param Request $request Http Request
     *
     * @throws AuthenticationException
     * @return mixed
     */
    public function updateStatus($id, Request $request)
    {
        $this->authorize('course.update');
        
        if ($course = Course::find($id)) {
            if (Auth::user()->cant('update', $course)) {
                throw new AuthenticationException();
            }
            $state = $request->post('active');
            if (in_array($state, [1, 0])) {
                if ($course->changeIsActiveState($state)) {
                    return $this->respondSuccess('Status updated');
                };
            }

            return $this->invalidArguments('Invalid State Value');

        }

        return $this->respondNotFound('User Not Found');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Course $course
     *
     * @return mixed
     * @throws AuthenticationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(Course $course)
    {
        $this->authorize('course.delete');

        if (Auth::user()->cant('delete', $course)) {
            throw new AuthenticationException();
        }

        try {
            $course->delete();
            return $this->respondSuccess();
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage());
        }
    }
}
