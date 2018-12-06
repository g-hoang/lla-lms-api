<?php

namespace App\Http\Controllers;

use App\Http\Requests\LessonRequest;
use App\Linkup\Facades\Search;
use App\Models\Lesson;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Http\Resources\Lesson as LessonResource;
use Illuminate\Support\Facades\Auth;

class LessonController extends ApiController
{
    /**
     * Display a listing of lessons by unit.
     *
     * @param Request $request Http Request
     *
     * @param $unit_id
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request, $unit_id)
    {
        $this->authorize('lesson.list');

        $request->request->add(['unit_id' => $unit_id]);

        $lesson = Search::lessons($request)->with('lessonType');

        if ($size = $request->get('pageSize')) {
            return LessonResource::collection($lesson->paginate((int)$size));
        }

        return LessonResource::collection($lesson->get());
    }


    /**
     * Store a newly created lesson.
     *
     * @param LessonRequest $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(LessonRequest $request)
    {
        $this->authorize('lesson.create');

        try {
            $lesson = Lesson::create(
                [
                    'title' => $request->title,
                    'unit_id' => $request->unit_id,
                    'lesson_type_id' => 1,
                    'language_focus' => "",
                ]
            );

            $last_lesson = Lesson::where('unit_id', $lesson->unit_id)
                ->orderBy('order', 'desc')
                ->first();

            if ($last_lesson) {
                $lesson->order = $last_lesson->order + 1;
                $lesson->save();
            } else {
                $lesson->order = 1;
                $lesson->save();
            }
        } catch (QueryException $e) {
            return $this->respondWithError("Record creation failed. code: " . $e->getMessage());
        }

        $msg = "Lesson created";
        return $this->respondCreated($msg, new LessonResource($lesson));
    }

    /**
     * Show lesson
     *
     * @param $id
     *
     * @return LessonResource|mixed
     * @throws AuthenticationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show($id)
    {
        $this->authorize('lesson.view');

        if ($lesson = Lesson::find($id)) {
            if (Auth::user()->cant('view', $lesson)) {
                throw new AuthenticationException();
            }

            return new LessonResource($lesson);
        }

        return $this->respondNotFound('Lesson Not Found');
    }


    /**
     * Update the specified resource in storage.
     *
     * @param LessonRequest $request
     * @param Lesson $lesson
     * @return mixed
     * @throws AuthenticationException
     * @throws \Throwable
     */
    public function update(LessonRequest $request, Lesson $lesson)
    {
        $this->authorize('lesson.update');

        if (Auth::user()->cant('update', $lesson)) {
            throw new AuthenticationException();
        }
        $data = $request->only(['title', 'lesson_type_id', 'language_focus', 'order', 'is_optional']);
        $lesson->fill($data);
        $lesson->saveOrFail();
        return $this->respondSuccess("Lesson updated.");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Lesson $lesson
     * @return mixed
     * @throws AuthenticationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(Lesson $lesson)
    {
        $this->authorize('lesson.delete');

        if (Auth::user()->cant('delete', $lesson)) {
            throw new AuthenticationException();
        }

        try {
            $lesson->delete();
            return $this->respondSuccess();
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage());
        }
    }

    /**
     * @param $lesson_id
     * @param Request $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function updateOrder($lesson_id, Request $request)
    {
        $list = $request->get('list', []);
        $this->authorize('lesson.update');

        foreach ($list as $i => $id) {
            $lesson = Lesson::find($id);
            $lesson->order = $i + 1;
            $lesson->saveOrFail();
        }

        return $this->respondSuccess('Order updated');
    }
}
