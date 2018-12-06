<?php

namespace App\Http\Controllers;

use App\Events\AutoAdvanceProgress;
use App\Events\LearnerActivationResend;
use App\Http\Requests\StoreLearner;
use App\Http\Resources\Learners as LearnersResource;
use App\Http\Resources\Learner as LearnerResource;
use App\Linkup\Facades\Search;
use App\Models\Course;
use App\Models\Learner;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LearnerController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return LearnersResource
     */
    public function index(Request $request)
    {
        // $this->authorize('leaner.list');

        $learners = Search::learners($request);

        $size = $request->get('pageSize', 20);

        return new LearnersResource($learners->paginate((int) $size));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreLearner $storeLearner
     * @return \Illuminate\Http\Response
     */
    public function store(StoreLearner $storeLearner)
    {
        // $this->authorize('leaner.create');

        DB::beginTransaction();

        try {
            $user = Learner::create(
                [
                    'email' => $storeLearner->email,
                    'firstname' => $storeLearner->first_name,
                    'lastname' => $storeLearner->last_name,
                    'dialingcode' => $storeLearner->dialling_code,
                    'phone' => $storeLearner->phone_number,
                    'address1' => $storeLearner->address1,
                    'address2' => $storeLearner->address2,
                    'town' => $storeLearner->town,
                    'country_id' => $storeLearner->country,
                    'zip' => $storeLearner->zip_code,
                    'center_id' => 1, // Default
                    'language_id' => $storeLearner->language
                ]
            );

            //$course = Course::find($storeLearner->course);
            // $user->courses()->save($course);

            $user->courses()->attach([$storeLearner->course => ['is_active' => true]]);

            DB::commit();

        } catch (Exception $e) {
            Log::error($e->getMessage());
            DB::rollback();

        }

//        $user->setRelation('courses', [$course]);
//        $user->push();

        $msg = "We've sent an invite to ".$user->email.", it should arrive in a few seconds";

        event(new AutoAdvanceProgress($user, env('AUTO_ADVANCE_LESSON_ID', 13)));

        return $this->respondCreated($msg, new LearnerResource($user));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //$this->authorize('leaner.view');

        $learner = Learner::with([
            'center' => function ($q) {
                $q->select('id', 'name');
            }
        ])->find($id);

        if ($learner) {
            return new LearnerResource($learner);
        }

        return $this->respondNotFound('Unit Not Found');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param StoreLearner $storeLearner
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(StoreLearner $storeLearner, $id)
    {
        if ($learner = Learner::find($id)) {
            DB::beginTransaction();

            try {
                $learner->fill(
                    [
                        'firstname' => $storeLearner->first_name,
                        'lastname' => $storeLearner->last_name,
                        'dialingcode' => $storeLearner->dialling_code,
                        'phone' => $storeLearner->phone_number,
                        'address1' => $storeLearner->address1,
                        'address2' => $storeLearner->address2,
                        'town' => $storeLearner->town,
                        'country_id' => $storeLearner->country,
                        'zip' => $storeLearner->zip_code,
                        'language_id' => $storeLearner->language,
                        'is_active' => $storeLearner->is_active
                    ]
                );

                foreach ($learner->courses->all() as $course) {
                    $course_ids[] = $course->id;

                    $data = ['is_active' => false];

                    if ($storeLearner->course == $course->id) {
                        $data = ['updated_at' => Carbon::now(), 'is_active' => true];
                    }

                    $learner->courses()->updateExistingPivot($course->id, $data);

                }

                if (!in_array($storeLearner->course, $course_ids)) {
                    $learner->courses()->attach([$storeLearner->course => ['is_active' => true]]);
                }

                $learner->pushAndLog();

                DB::commit();

            } catch (Exception $e) {
                Log::error($e->getMessage());
                DB::rollback();

            }

            return $this->respondSuccess('Learner updated.', new LearnerResource($learner));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return void
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Resend learner activation email
     *
     * @param Request $request
     * @return mixed
     */
    public function resendActivationEmail(Request $request)
    {
        if($learner = Learner::find($request->learner_id)){

            if(!$learner->is_active){
                return $this->respondNotFound('Learner not active');
            }

            if($learner->status == 'REGISTERED'){
                return $this->respondNotFound('Learner already registered');
            }

            event(new LearnerActivationResend($learner));

            return $this->respondSuccess('Activation email has been sent!');
        }

        return $this->respondNotFound('Learner not found');
    }
}
