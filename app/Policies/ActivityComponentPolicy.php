<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ActivityComponent;
use Illuminate\Auth\Access\HandlesAuthorization;

class ActivityComponentPolicy
{
    use HandlesAuthorization;

    public function before($user, $ability)
    {
        if ($user->role_id == 1) { //Admin Role
            return true;
        }
    }

    /**
     * Determine whether the user can view the Lesson.
     *
     * @param  \App\Models\User $user
     * @param ActivityComponent $obj
     * @return mixed
     */
    public function view(User $user, ActivityComponent $obj)
    {
        return true;
    }

    /**
     * Determine whether the user can update the Lesson.
     *
     * @param  \App\Models\User $user
     * @param ActivityComponent $obj
     * @return mixed
     */
    public function update(User $user, ActivityComponent $obj)
    {
        return true;
    }

    /**
     * Determine whether the user can delete the Lesson.
     *
     * @param User $user
     * @param ActivityComponent $obj
     * @return mixed
     */
    public function delete(User $user, ActivityComponent $obj)
    {
        return true;
    }
}
