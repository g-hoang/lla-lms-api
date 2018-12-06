<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\Auth;

class UserLog
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $data;

    /**
     * Create a new event instance.
     *
     * @param $id
     * @param $event
     * @param $details
     */
    public function __construct($id, $event, $details = "")
    {
        $this->data['id'] = $id;
        $this->data['event'] = $event;
        $this->data['details'] = $details;
        $this->user = Auth::user();
    }
}
