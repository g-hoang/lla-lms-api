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

class Track
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $learner;
    public $activity_id;
    public $type;
    public $extra;

    /**
     * Create a new event instance.
     *
     * @param $activity_id
     * @param $type
     * @param null $extra
     * @param null $learner
     */
    public function __construct($activity_id, $type, $extra = null, $learner = null)
    {
        $this->activity_id = $activity_id;
        $this->type = $type;
        $this->extra = $extra;
        if ($learner) {
            $this->learner = $learner;
        } else {
            $this->learner = Auth::user();
        }
    }
}
