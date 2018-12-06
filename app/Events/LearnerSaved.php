<?php

namespace App\Events;

use App\Models\Learner;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class LearnerSaved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $learner;
    public $actor;
    
    /**
     * LearnerSaved constructor.
     *
     * @param Learner $learner
     */
    public function __construct(Learner $learner)
    {
        $this->learner = $learner;
        $this->actor = auth()->user();
    }

}
