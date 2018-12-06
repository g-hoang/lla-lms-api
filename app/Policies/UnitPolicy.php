<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Unit;
use Illuminate\Auth\Access\HandlesAuthorization;

class UnitPolicy
{
    use HandlesAuthorization;

    public function before($user, $ability)
    {
        if ($user->role_id == 1) { //Admin Role
            return true;
        }
    }

    /**
     * Determine whether the user can view the Unit.
     *
     * @param  \App\Models\User $user
     * @param Unit $obj
     * @return mixed
     */
    public function view(User $user, Unit $obj)
    {
        return true;
    }

    /**
     * Determine whether the user can update the Unit.
     *
     * @param  \App\Models\User $user
     * @param Unit $obj
     * @return mixed
     */
    public function update(User $user, Unit $obj)
    {
        return true;
    }

    /**
     * Determine whether the user can delete the Unit.
     *
     * @param User $user
     * @param Unit $obj
     * @return mixed
     */
    public function delete(User $user, Unit $obj)
    {
        return true;
    }
}
