<?php
/**
 * Created by PhpStorm.
 * User: kenath
 * Date: 12/20/2017
 * Time: 3:12 PM
 */

namespace App\Listeners;

use App\Mail\AdminActivationSent;
use App\Models\Userlogs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Token;

class UserEventSubscriber
{

    /**
     * @var \Illuminate\Contracts\Auth\Authenticatable|null
     */
    protected $actor;

    /**
     * UserEventSubscriber constructor.
     */
    function __construct()
    {
        $this->actor = Auth::user();
    }

    /**
     * Handle user login events.
     *
     * @param null $event Object
     *
     * @return void
     */
    public function onUserCreate($event)
    {
        $this->log($event, Config::get('user_events.CREATE'));

        if ($event->actor) {
            Mail::to($event->user)
                ->queue(new AdminActivationSent($event->user));

            $this->log($event, Config::get('user_events.INVITE'));
        }
    }

    /**
     * Handle user activate event.
     *
     * @param null $event Object
     *
     * @return void
     */
    public function onUserActivate($event)
    {
        $this->log($event, Config::get('user_events.ACTIVATE'));
    }

    /**
     * Handle user state change event.
     *
     * @param null $event Object
     *
     * @return void
     */
    public function onUserStatusChange($event)
    {

        if ($event->user->is_active == false) {
            if ($token = $event->user->latest_jwt_claims) {
                try {
                    JWTAuth::manager()->invalidate(new Token($token));
                } catch (TokenInvalidException $exception) {
                    \Log::info('push', ['description'=> 'Invalid Token signature']);
                } catch (TokenExpiredException $exception) {
                    \Log::info('push', ['description'=> 'Token already expired']);
                } catch (JWTException $exception) {
                    \Log::info('push', ['description'=> 'Token invalid']);
                }

                $event->user->setJWT(null);

            }
        }

        $this->log($event, Config::get('user_events.STATUS_CHANGE'));
    }

    /**
     * Resend admin activation email
     *
     * @param $event
     */
    public function onLearnerActivationResend($event)
    {
        $this->log($event, Config::get('user_events.ADMIN_ACTIVATION_RESEND'));

        Mail::to($event->user)
            ->queue(new AdminActivationSent($event->user));
    }

    /**
     * Log
     *
     * @param $event
     * @param $event_type
     */
    private function log($event, $event_type)
    {
        $user_created = null;
        if ($this->actor) {
            $user_created = $this->actor->getAuthIdentifier();
        }

        $details = "";
        if (!$user_created) {
            $details = "System Event.\n";
        }

        Userlogs::create([
            'user_id' => $user_created ? $user_created : 1,
            'foreign_key' => $event->user->id,
            'event' => $event_type,
            'details' => $details ? $details : $event->user->toJson()
        ]);
    }

    /**
     * Map listeners and events.
     *
     * @param null $events Object
     *
     * @return void
     */
    public function subscribe($events)
    {
        $events->listen(
            'App\Events\UserCreated',
            'App\Listeners\UserEventSubscriber@onUserCreate'
        );
        $events->listen(
            'App\Events\UserActivated',
            'App\Listeners\UserEventSubscriber@onUserActivate'
        );
        $events->listen(
            'App\Events\UserStatusChanged',
            'App\Listeners\UserEventSubscriber@onUserStatusChange'
        );
        $events->listen(
            'App\Events\AdminActivationResend',
            'App\Listeners\UserEventSubscriber@onLearnerActivationResend'
        );
    }
}
