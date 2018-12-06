<?php
/**
 * Created by PhpStorm.
 * User: drevil
 * Date: 12/17/17
 * Time: 7:58 PM
 */

namespace App\Http\Controllers;

use App\Events\UserForgotPassword;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\PasswordReset;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Resources\User as UserResource;

class AuthController extends ApiController
{
    protected $user = null;

    /**
     * AuthController constructor.
     */
    public function __construct()
    {
        $this->user = new User;
    }

    /**
     * Login
     *
     * @param Request $request Http Request
     *
     * @return mixed
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $credentials['is_active'] = true;
        $credentials['status'] = 'REGISTERED';

        $claims = ['type' => 'admin', 'email' => $credentials['email']];

        try {
            if (! $token = JWTAuth::claims($claims)->attempt($credentials)) {
                return Response::json(['error' => 'Invalid Credentials'], 401);
            }
        } catch (JWTException $e) {
            return Response::json(['error' => 'token_exception'], 500);
        }

        // all good so return the token
        Auth::user()->setJWT($token);

        return Response::json(compact('token'));
    }

    /**
     * Logout
     *
     * @return mixed
     */
    public function logout()
    {
        Auth::user()->setJWT(null);

        JWTAuth::parseToken()->invalidate();

        return Response::json(['data' => 'logged_out']);
    }

    /**
     * Logged in user
     *
     * @param Request $request http request
     *
     * @return mixed
     */
    public function me(Request $request)
    {
        $user = JWTAuth::toUser($request->token);

        return $this->respond(
            [ 'data' => new UserResource($user) ]
        );
    }

    /**
     * Forgot password
     *
     * @param ForgotPasswordRequest $request
     *
     * @return mixed
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        try {
            if ($user = User::getValidUserByEmail($request->email)) {
                event(new UserForgotPassword($user));

                return $this->respond(['message' => 'Please check your email to reset your password']);
            }

            return $this->respondForUnauthorizedRequest('This email address is not valid');

        } catch (\Exception $e) {
            return $this->respondInternalError($e->getMessage());
        }
    }

    /**
     * Check token and email and redirect accordingly
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function resetPasswordHandler(Request $request)
    {
        $email = htmlspecialchars($request->get('email'));
        $token = htmlspecialchars($request->get('token'));

        if (!$email || !$token) {
            return Redirect::to(env('FRONTEND_URL').'/login');
        }

        try {
            if ($password_reset = PasswordReset::isValid($email, $token, User::class)) {
                if ($password_reset->notExpired()) {
                    return Redirect::to(env('FRONTEND_URL').'/reset-password?email='.$email.'&token='.$token);
                }

            }

            return Redirect::to(env('FRONTEND_URL').'/login');

        } catch (\Exception $e) {
            return $this->respondInternalError($e->getMessage());
        }
    }

    /**
     * Reset password
     *
     * @param ResetPasswordRequest $request
     *
     * @return mixed
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            if (!$user = User::getValidUserByEmail($request->email)) {
                return $this->respondForBadRequest('This email address is not valid');
            }

            if ($password_reset = $user->latestPasswordReset()->valid($request->token)) {
                if ($password_reset->notExpired()) {
                    DB::beginTransaction();

                    $user->fill(['password' => bcrypt($request->password)])->save();

                    $password_reset->fill(['is_changed' => true])->save();

                    $token = JWTAuth::claims($user->generateJWTClaims())
                        ->attempt($user->generateJWTCredentials($request->email, $request->password));

                    $user->setJWT($token);

                    DB::commit();

                    return $this->respond([
                        'message' => 'Password successfully updated!',
                        'token' => $token
                    ]);
                }

                return $this->respondForBadRequest('The link has expired');

            }

            return $this->respondForBadRequest('The token is invalid');

        } catch (\Exception $e) {
            DB::rollback();

            return $this->respondInternalError($e->getMessage());
        }
    }
}