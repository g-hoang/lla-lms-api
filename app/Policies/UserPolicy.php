<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function before($user, $ability)
    {
        if ($user->role_id == 1) { //Admin Role
            return true;
        }
    }

    /**
     * Determine whether the user can view the user.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $obj
     * @return mixed
     */
    public function view(User $user, User $obj)
    {
        // For now only admins and user himself can see his info

        return $user->id == $obj->id;
    }

    /**
     * Determine whether the user can update the user.
     *
     * @param  \App\Models\User $user
     * @param User $obj
     * @return mixed
     */
    public function update(User $user, User $obj)
    {
        // For now only admins and user himself can update his info

        return $user->id == $obj->id;
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param User $user
     * @param User $obj
     * @return mixed
     */
    public function delete(User $user, User $obj)
    {
        // For now only other admins can delete account

        return $user->id != $obj->id;
    }
}
