<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class UserActivated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;

    /**
     * UserActivated constructor.
     *
     * @param User $user Object
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
