<?php

namespace App\Listeners;

use App\Events\UserForgotPassword;
use App\Mail\UserPasswordResetSent;
use App\Models\PasswordReset;
use Carbon\Carbon;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendUserForgotPasswordEmail
{

    /**
     * Handle the event.
     *
     * @param  UserForgotPassword  $event
     * @return void
     */
    public function handle(UserForgotPassword $event)
    {

        $passwordReset = new PasswordReset;

        $passwordReset->fill([
            'email' => $event->user->email,
            'token' => md5($event->user->email.Carbon::now()->toAtomString()),
            'is_changed' => false,
        ]);

        $event->user
            ->passwordReset()
            ->save($passwordReset);

        Mail::to($event->user)
            ->queue(new UserPasswordResetSent($event->user, $passwordReset->token));
    }
}
