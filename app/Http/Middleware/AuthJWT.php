<?php
/**
 * Created by PhpStorm.
 * User: drevil
 * Date: 12/17/17
 * Time: 7:49 PM
 */

namespace App\Http\Middleware;

use App\Models\Learner;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class AuthJWT
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = JWTAuth::getToken();
        try {
            $payload = JWTAuth::parseToken()->getPayload();
        } catch (Exception $e) {
            return response()->json(['error'=>'Token is Invalid'], 401);
        }
        if ($payload->get('type') == 'learner') {
            Auth::shouldUse('learner');
        }

        try {
            if ($request->route()->getPrefix() == 'learner' && $payload->get('type') == 'admin') {
                $user = Learner::find(1);
                $token = JWTAuth::fromUser($user);
                JWTAuth::setToken($token);
            }

            $user = JWTAuth::toUser($token);

            if ($payload->get('type') != 'admin') {
                if (strcasecmp($payload->get('email'), $user->email) != 0) {
                    throw new TokenInvalidException('emails does not match.');
                }
            }
        } catch (Exception $e) {
            if ($e instanceof TokenInvalidException) {
                return response()->json(['error'=>'Token is Invalid']);
            } elseif ($e instanceof TokenExpiredException) {
                return response()->json(['error'=>'Token is Expired']);
            } else {
                return response()->json(['error'=>'Something is wrong']);
            }
        }
        return $next($request);
    }
}
