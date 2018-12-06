<?php

namespace App\Events;

use App\Models\Learner;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class LearnerActivationResend
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $learner;

    public $actor;


    /**
     * LearnerActivationResend constructor.
     * @param Learner $learner
     */
    public function __construct(Learner $learner)
    {
        $this->learner = $learner;
        $this->actor = auth()->user();
    }
}
