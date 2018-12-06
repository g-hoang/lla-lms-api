<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Course;
use Illuminate\Auth\Access\HandlesAuthorization;

class CoursePolicy
{
    use HandlesAuthorization;

    public function before($user, $ability)
    {
        if ($user->role_id == 1) { //Admin Role
            return true;
        }
    }

    /**
     * Determine whether the user can view the Course.
     *
     * @param  \App\Models\User $user
     * @param Course $obj
     * @return mixed
     */
    public function view(User $user, Course $obj)
    {
        return true;
    }

    /**
     * Determine whether the user can update the Course.
     *
     * @param  \App\Models\User $user
     * @param Course $obj
     * @return mixed
     */
    public function update(User $user, Course $obj)
    {
        return true;
    }

    /**
     * Determine whether the user can delete the Course.
     *
     * @param User $user
     * @param Course $obj
     * @return mixed
     */
    public function delete(User $user, Course $obj)
    {
        return true;
    }
}
