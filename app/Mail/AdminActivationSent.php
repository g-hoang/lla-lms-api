<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class AdminActivationSent
 *
 * @package App\Mail
 * @author  kenath <kenath@ceylonit.com>
 */
class AdminActivationSent extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $user;

    /**
     * AdminActivationSent constructor.
     *
     * @param User $user Object
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.admin.activation')
            ->with(['user' => $this->user])
            ->subject('LinkUP: Admin Account Activation');
    }
}
