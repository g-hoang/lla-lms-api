<?php

namespace App\Http\Controllers\Learner;

use App\Events\Progress;
use App\Events\Track;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Learner\ActivityRequest;
use App\Linkup\Facades\Search;
use App\Models\Activity;
use Illuminate\Http\Request;
use App\Http\Resources\Learner\ActivityComponent as ActivityComponentResource;
use App\Http\Resources\Learner\Activity as ActivityResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class TrackController extends ApiController
{
    protected $gate = 'learner';

    public function __construct()
    {
        Auth::shouldUse('learner');
    }

    /**
     * track activity attempts, skipping, completion and exits.
     *
     * @param $request Request
     *
     * @param $activity_id
     * @return mixed
     */
    public function track(Request $request, $activity_id)
    {
        $data = $request->all();
        event(new Track($activity_id, $data['type']));
        return ['OK'];
    }
}
