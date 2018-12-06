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

class ActivityResponse
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $learner;
    public $activity;
    public $data;

    /**
     * Create a new event instance.
     *
     * @param $activity
     * @param $data
     */
    public function __construct($activity, $data)
    {
        $this->learner = Auth::user();
        $this->activity = $activity;
        $this->data = $data;
    }
}
