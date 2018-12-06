<?php
/**
 * Created by PhpStorm.
 * User: kenath
 * Date: 4/23/2018
 * Time: 11:18 AM
 */

namespace App\Listeners;


use App\Mail\LearnerActivationSent;
use App\Models\LearnerLog;
use App\Models\Userlogs;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Token;

class LearnerEventSubscriber
{
    protected $actor;

    /**
     * UserEventSubscriber constructor.
     */
    function __construct()
    {
        $this->actor = auth()->user();
    }

    /**
     * Handle user login events.
     *
     * @param null $event Object
     *
     * @return void
     */
    public function onLearnerCreate($event)
    {
        $this->log($event, Config::get('user_events.LEARNER_CREATED'));

        if ($event->actor) {
            Mail::to($event->learner)
                ->queue(new LearnerActivationSent($event->learner));
        }
    }


    /**
     * Handle user login events.
     *
     * @param null $event Object
     *
     * @return void
     */
    public function onLearnerActivate($event)
    {
        if($event->actor && $event->actor->id){

            $this->log($event, Config::get('user_events.LEARNER_ACTIVATED'));

            return;
        }

        LearnerLog::create([
            'learner_id' => $event->learner->id,
            'foreign_key' => $event->learner->id,
            'event' => Config::get('learner_events.LEARNER_REGISTERED'),
            'details' => 'Self activated'
        ]);
    }

    /**
     * Handle user login events.
     *
     * @param null $event Object
     *
     * @return void
     */
    public function onLearnerSave($event)
    {

        $this->log($event, Config::get('user_events.LEARNER_UPDATED'));

    }

    /**
     * Resend learner activation email
     *
     * @param $event
     */
    public function onLearnerActivationResend($event)
    {
        $this->log($event, Config::get('user_events.LEARNER_ACTIVATION_RESEND'));

        Mail::to($event->learner)
            ->queue(new LearnerActivationSent($event->learner));
    }

    /**
     * Deactivate learner
     *
     * @param $event
     */
    public function onLearnerDeactivate($event)
    {

        if ($token = $event->learner->latest_jwt_claims) {

            try{
                JWTAuth::manager()->invalidate(new Token($token));
            } catch (TokenInvalidException $exception) {
                \Log::info('push', ['description'=> 'Invalid Token signature']);
            } catch (TokenExpiredException $exception) {
                \Log::info('push', ['description'=> 'Token already expired']);
            } catch (JWTException $exception) {
                \Log::info('push', ['description'=> 'Token invalid']);
            }

            $event->learner->setJWT(null);

        }

        Userlogs::create([
            'user_id' => $event->actor->id,
            'foreign_key' => $event->learner->id,
            'event' => Config::get('user_events.LEARNER_DEACTIVATED'),
            'details' => $event->learner->toJson()
        ]);
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
            'foreign_key' => $event->learner->id,
            'event' => $event_type,
            'details' => $details ? $details : $event->learner->toJson()
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
            'App\Events\LearnerCreated',
            'App\Listeners\LearnerEventSubscriber@onLearnerCreate'
        );
        $events->listen(
            'App\Events\LearnerActivated',
            'App\Listeners\LearnerEventSubscriber@onLearnerActivate'
        );
        $events->listen(
            'App\Events\LearnerSaved',
            'App\Listeners\LearnerEventSubscriber@onLearnerSave'
        );
        $events->listen(
            'App\Events\LearnerDeactivated',
            'App\Listeners\LearnerEventSubscriber@onLearnerDeactivate'
        );
        $events->listen(
            'App\Events\LearnerActivationResend',
            'App\Listeners\LearnerEventSubscriber@onLearnerActivationResend'
        );

    }
}
