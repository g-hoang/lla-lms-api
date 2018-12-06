<?php

namespace App\Mail;

use App\Models\Learner;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class LearnerActivationSent
 *
 * @package App\Mail
 * @author  kenath <kenath@ceylonit.com>
 */
class LearnerActivationSent extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $learner;

    /**
     * LearnerActivationSent constructor.
     *
     * @param Learner $learner
     * @internal param Learner|User $user Object
     */
    public function __construct(Learner $learner)
    {
        $this->learner = $learner;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.learner.activation')
            ->with(['learner' => $this->learner])
            ->subject('LinkUP: Learner Account Activation');
    }
}
