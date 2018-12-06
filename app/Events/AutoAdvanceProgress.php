<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class AutoAdvanceProgress
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $learner;
    public $lesson;

    /**
     * Create a new event instance.
     *
     * @param $learner
     * @param $uptoLesson
     */
    public function __construct($learner, $uptoLesson)
    {
        $this->learner = $learner;
        $this->lesson = $uptoLesson;
    }

}
