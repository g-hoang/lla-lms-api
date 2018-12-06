<?php

namespace App\Linkup\Observers;

use App\Events\UserActivated;
use App\Events\UserCreated;
use App\Events\UserStatusChanged;
use App\Models\User;

/**
 * Class UserObserver
 *
 * @package App\Linkup\Observers
 */
class UserObserver
{

    /**
     * Listen to user creating
     *
     * @param User $user object
     *
     * @return null
     */
    public function creating(User $user)
    {
        $user->email_token = $this->generateUniqueToken();
    }

    /**
     * Listen to the User created event.
     *
     * @param User $user Object
     *
     * @return void
     */
    public function created(User $user)
    {
        event(new UserCreated($user));
    }

    /**
     * Listen to the User deleting event.
     *
     * @param \App\Models\User $user Object
     *
     * @return void
     */
    public function deleting(User $user)
    {
        //
    }

    /**
     * Generate Unique token
     *
     * @return string
     */
    protected function generateUniqueToken()
    {
        if (env('APP_ENV') == 'production') {
            return md5(uniqid(rand(), true));
        } else {
            return md5('counter-strike'); // for QA automation purpose
        }
    }

    /**
     * Activate User
     *
     * @param User $user User Object
     *
     * @return void
     */
    public function activated(User $user)
    {
        event(new UserActivated($user));
    }

    /**
     * User Status Change
     *
     * @param User $user User Object
     *
     * @return void
     */
    public function statusChanged(User $user)
    {
        event(new UserStatusChanged($user));
    }

}
