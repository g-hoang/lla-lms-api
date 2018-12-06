<?php

namespace App\Mail;

use App\Models\Learner;
use App\Models\PasswordReset;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class AdminActivationSent
 *
 * @package App\Mail
 * @author  kenath <kenath@ceylonit.com>
 */
class PasswordResetSent extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $user;
    protected $token;

    /**
     * PasswordResetSent constructor.
     * @param Learner $learner
     * @param $token
     */
    public function __construct(Learner $learner, $token)
    {
        $this->user = $learner;
        $this->token = $token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.learner.reset')
            ->with([
                'user' => $this->user ,
                'token' => $this->token
            ])
            ->subject('LinkUP: Password Reset');
    }
}
