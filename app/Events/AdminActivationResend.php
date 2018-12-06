<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class AdminActivationResend
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;

    public $actor;


    /**
     * AdminActivationResend constructor.
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;

        $this->actor = auth()->user();
    }
}
