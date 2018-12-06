<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Support\Facades\Auth;

class UserCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $actor;

    /**
     * AdminCreated constructor.
     *
     * @param User $user Object
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->actor = Auth::user();
    }

}
