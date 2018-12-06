<?php

namespace App\Http\Controllers;

use App\Http\Requests\UnitRequest;
use App\Jobs\UpdateF2FAttendance;
use App\Linkup\Facades\Search;
use App\Models\LearnerUnits;
use App\Models\Unit;
use App\Policies\UnitPolicy;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Http\Resources\Unit as UnitResource;
use Illuminate\Support\Facades\Auth;
use Mockery\Exception;

class UnitController extends ApiController
{
    /**
     * Display a listing of units by course.
     *
     * @param Request $request Http Request
     *
     * @param $course_id
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request, $course_id)
    {
        $this->authorize('unit.list');

        $request->request->add(['course_id' => $course_id]);

        $course = Search::units($request);

        if ($size = $request->get('pageSize')) {
            return UnitResource::collection($course->paginate($size));
        }

        return UnitResource::collection($course->get());
    }


    /**
     * Store a newly created unit.
     *
     * @param UnitRequest $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(UnitRequest $request)
    {
        $this->authorize('unit.create');

        try {
            $unit = Unit::create(
                [
                    'title' => $request->title,
                    'course_id' => $request->course_id
                ]
            );

            $last_unit = Unit::where('course_id', $unit->course_id)
                ->orderBy('order', 'desc')
                ->first();

            if ($last_unit) {
                $unit->order = $last_unit->order + 1;
                $unit->save();
            } else {
                $unit->order = 1;
                $unit->save();
            }
        } catch (QueryException $e) {
            return $this->respondWithError("Record creation failed. code: " . $e->getCode());
        }

        $msg = "Unit created";
        return $this->respondCreated($msg, new UnitResource($unit));
    }

    /**
     * Show unit
     *
     * @param $id
     *
     * @return UnitResource|mixed
     * @throws AuthenticationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show($id)
    {
        $this->authorize('unit.view');

        if ($unit = Unit::find($id)) {
            if (Auth::user()->cant('view', $unit)) {
                throw new AuthenticationException();
            }

            return new UnitResource($unit);
        }

        return $this->respondNotFound('Unit Not Found');
    }


    /**
     * Update the specified resource in storage.
     *
     * @param UnitRequest $request
     * @param Unit $unit
     * @return mixed
     * @throws AuthenticationException
     * @throws \Throwable
     */
    public function update(UnitRequest $request, Unit $unit)
    {
        $this->authorize('unit.update');

        if (Auth::user()->cant('update', $unit)) {
            throw new AuthenticationException();
        }
        $data = $request->only(['title']);
        $unit->fill($data);
        $unit->saveOrFail();
        return $this->respondSuccess('Unit updated', $data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Unit $unit
     * @return mixed
     * @throws AuthenticationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(Unit $unit)
    {
        $this->authorize('unit.delete');

        if (Auth::user()->cant('delete', $unit)) {
            throw new AuthenticationException();
        }

        try {
            $unit->delete();
            return $this->respondSuccess();
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage());
        }
    }

    /**
     * @param $unit_id
     * @param Request $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function updateOrder($unit_id, Request $request)
    {
        $list = $request->get('list', []);
        $this->authorize('unit.update');

        $course_id = 0;
        foreach ($list as $i => $id) {
            $unit = Unit::find($id);
            $course_id = $unit->course_id;
            $unit->order = $i + 1;
            $unit->saveOrFail();
        }

        // UpdateF2FAttendance::dispatch(['course_id' => $course_id, 'unit_ids' => $list]);

        return $this->respondSuccess('Order updated');
    }

    public function setF2FClassStatus($unit_id, Request $request)
    {
        $learner_id = $request->get('learner_id');
        $status = $request->get('status', false);
        $this->authorize('unit.update');

        LearnerUnits::changeF2FStatus($learner_id, $unit_id, $status);

        return $this->respondSuccess('Status updated');
    }
}
