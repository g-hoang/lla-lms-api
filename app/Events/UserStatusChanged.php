<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class UserStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $actor;

    /**
     * UserStatusChanged constructor.
     *
     * @param User $user Object
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->actor = auth()->user();
    }

}
