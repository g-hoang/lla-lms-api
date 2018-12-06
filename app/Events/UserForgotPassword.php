<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class UserForgotPassword
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;

    /**
     * UserForgotPassword constructor.
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
