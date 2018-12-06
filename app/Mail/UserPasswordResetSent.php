<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserPasswordResetSent extends Mailable
{
    use Queueable, SerializesModels;

    protected $user;
    protected $token;

    /**
     * UserPasswordResetSent constructor.
     * @param User $user
     * @param $token
     */
    public function __construct(User $user, $token)
    {
        $this->user = $user;
        $this->token = $token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.admin.reset')
            ->with([
                'user' => $this->user ,
                'token' => $this->token
            ])
            ->subject('LinkUP: Password Reset');
    }
}
