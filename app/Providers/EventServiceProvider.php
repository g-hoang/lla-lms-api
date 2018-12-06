<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\UserCreated' => [],
        'App\Events\UserActivated' => [],
        'App\Events\UserStatusChanged' => [],
        'App\Events\UserLog' => ['App\Listeners\UserEventLogger'],
        'App\Events\AdminActivationResend' => [],

        'App\Events\LearnerCreated' => [],
        'App\Events\LearnerActivated' => [],
        'App\Events\LearnerDeactivated' => [],
        'App\Events\LearnerSaved' => [],
        'App\Events\LearnerActivationResend' => [],

        'App\Events\ActivityResponse' => ['App\Listeners\ActivityResponseListener'],
        'App\Events\AutoAdvanceProgress' => ['App\Listeners\AutoAdvanceProgressListener'],
        'App\Events\UserForgotPassword' => ['App\Listeners\SendUserForgotPasswordEmail'],
    ];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        'App\Listeners\UserEventSubscriber',
        'App\Listeners\LearnerEventSubscriber',
        'App\Listeners\ProgressEventSubscriber'
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
