<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\Auth;

class Progress
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $learner;
    public $activity;

    /**
     * Create a new event instance.
     *
     * @param $activity
     * @param null $learner
     */
    public function __construct($activity, $learner = null)
    {
        $this->activity = $activity;
        if ($learner) {
            $this->learner = $learner;
        } else {
            $this->learner = Auth::user();
        }
    }
}
