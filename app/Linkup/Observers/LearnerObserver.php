<?php
/**
 * Created by PhpStorm.
 * User: kenath
 * Date: 4/23/2018
 * Time: 11:12 AM
 */

namespace App\Linkup\Observers;

use App\Events\LearnerActivated;
use App\Events\LearnerCreated;
use App\Events\LearnerDeactivated;
use App\Events\LearnerSaved;
use App\Models\Learner;

class LearnerObserver
{
    /**
     * Listen to the User created event.
     *
     * @param Learner $learner
     * @return void
     *
     */
    public function created(Learner $learner)
    {
        event(new LearnerCreated($learner));
    }

    /**
     * Listen to user creating
     *
     * @param Learner $learner object
     *
     * @return null
     */
    public function creating(Learner $learner)
    {
        $learner->email_token = md5(uniqid(rand(), true));
    }

    /**
     * Registration Completed
     *
     * @param Learner $learner
     * @return void
     *
     */
    public function activated(Learner $learner)
    {
        event(new LearnerActivated($learner));
    }

    /**
     * Registration Completed
     *
     * @param Learner $learner
     * @return void
     *
     */
    public function fieldsUpdated(Learner $learner)
    {
        event(new LearnerSaved($learner));
    }

    /**
     * @param Learner $learner
     * @return void
     */
    public function updating(Learner $learner)
    {
        $original = $learner->getOriginal();

        if ($learner->is_active != $original['is_active']) {
            if (!$learner->is_active) {
                event(new LearnerDeactivated($learner));
            } else {
                event(new LearnerActivated($learner));
            }

        }
    }
}