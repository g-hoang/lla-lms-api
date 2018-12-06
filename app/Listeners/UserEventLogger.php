<?php

namespace App\Listeners;

use App\Events\UserLog;
use App\Models\Userlogs;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Config;

class UserEventLogger
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  UserLog  $event
     * @return void
     */
    public function handle(UserLog $event)
    {
        if (!$event->user || !$event->user->id) {
            $event->data['details'] = "System Event.\n" . $event->data['details'];
            $event->user = (object) ['id' => 1];
        }

        $record = new Userlogs();
        $record->user_id = $event->user->id ? $event->user->id : 1;
        $record->foreign_key = $event->data['id'];
        $record->event = Config::get('user_events.' . $event->data['event'])?Config::get('user_events.' . $event->data['event']): '';
        $record->details = $event->data['details'];
        $record->save();
    }
}
