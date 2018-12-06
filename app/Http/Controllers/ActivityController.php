<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActivityRequest;
use App\Http\Resources\ActivityComponent as ActivityComponentResource;
use App\Linkup\Facades\Search;
use App\Models\Activity;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Http\Resources\Activity as ActivityResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class ActivityController extends ApiController
{
    /**
     * Display a listing of activities by lesson.
     *
     * @param Request $request Http Request
     *
     * @param $lesson_id
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request, $lesson_id)
    {
        $this->authorize('activity.list');

        $request->request->add(['lesson_id' => $lesson_id]);

        $activities = Search::activities($request)->with('textOutputs');

        $enabled_activities = $disabled_activities = [];

        foreach ($activities->get() as $activity) {
            if ($activity->is_disabled) {
                $disabled_activities[] = $activity;
                continue;
            }
            $enabled_activities[] = $activity;
        }

        return $this->respond([
            'enabled_activities' => ActivityResource::collection(collect($enabled_activities)),
            'disabled_activities' => ActivityResource::collection(collect($disabled_activities))
        ]);
    }


    /**
     * Store a newly created activity.
     *
     * @param ActivityRequest $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(ActivityRequest $request)
    {
        $this->authorize('activity.create');

        try {
            $activity = Activity::create(
                [
                    'title' => $request->title,
                    'lesson_id' => $request->lesson_id,
                    'focus' => "General",
                    'instructions' => "",
                ]
            );

            $last_activity = Activity::where('lesson_id', $activity->lesson_id)
                ->orderBy('order', 'desc')
                ->first();

            if ($last_activity) {
                $activity->order = $last_activity->order + 1;
                $activity->save();
            } else {
                $activity->order = 1;
                $activity->save();
            }
        } catch (QueryException $e) {
            return $this->respondWithError("Record creation failed. code: " . $e->getMessage());
        }

        $msg = "Activity created";
        return $this->respondCreated($msg, new ActivityResource($activity));
    }

    /**
     * Show activity
     *
     * @param $id
     *
     * @return ActivityResource|mixed
     * @throws AuthenticationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show($id)
    {
        $this->authorize('activity.view');

        if ($activity = Activity::with('lesson')->find($id)) {
            if (Auth::user()->cant('view', $activity)) {
                throw new AuthenticationException();
            }

            return new ActivityResource($activity);
        }

        return $this->respondNotFound('Activity Not Found');
    }


    /**
     * Update the specified resource in storage.
     *
     * @param ActivityRequest $request
     * @param Activity $activity
     * @return mixed
     * @throws AuthenticationException
     * @throws \Throwable
     */
    public function update(ActivityRequest $request, Activity $activity)
    {
        $this->authorize('activity.update');

        if (Auth::user()->cant('update', $activity)) {
            throw new AuthenticationException();
        }

        $data = $request->only(['title', 'instructions', 'focus', 'order', 'is_optional', 'max_time', 'auto_advance_timer', 'is_disabled']);

        $activity->updateAndSync($data);

        return $this->respondSuccess('Activity updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Activity $activity
     * @return mixed
     * @throws AuthenticationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(Activity $activity)
    {
        $this->authorize('activity.delete');

        if (Auth::user()->cant('delete', $activity)) {
            throw new AuthenticationException();
        }

        try {
            $activity->delete();
            return $this->respondSuccess();
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage());
        }
    }

    /**
     * @param $activity_id
     * @param Request $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function updateOrder($activity_id, Request $request)
    {
        $list = $request->get('list', []);
        $this->authorize('activity.update');

        foreach ($list as $i => $id) {
            $activity = Activity::find($id);
            $activity->order = $i + 1;
            $activity->saveOrFail();
        }

        return $this->respondSuccess('Order updated');
    }

    /**
     * @return mixed
     */
    public function languageFocusTypes()
    {
        $language_focus = Config::get('enums.language_focus');

        return $this->respond([
           'data' => array_map(function ($k, $val) {
               return ['id' => $k, 'name' => $val];
           }, array_keys($language_focus), $language_focus)
        ]);
    }

    /**
     * Get All components linked with the activity
     *
     * @param $activity_id
     * @return mixed
     */
    public function components($activity_id)
    {
        $activity = Activity::find($activity_id);

        if (!$activity) {
            return $this->respondNotFound('Activity Not Found');
        }

        $components = $activity->components()->orderBy('order', 'ASC')->get();

        return  ActivityComponentResource::collection($components);
    }

    /**
     * @param $activity_id
     * @return mixed
     */
    public function textOutputs($activity_id)
    {
        if ($activity = Activity::with('textOutputs')->find($activity_id)) {
            $collection = [];

            foreach ($activity->textOutputs as $component) {
                if ($related_activity = $component->getRelatedActivityAttribute()) {
                    $return = $component->toArray();
                    $return['related_activity'] = $related_activity;
                    $collection[] = $return;

                }

            }

            return $this->respond($collection);
        }

        return $this->respondNotFound('Activity not found');
    }
}
