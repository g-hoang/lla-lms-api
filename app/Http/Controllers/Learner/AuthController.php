<?php
/**
 * Created by PhpStorm.
 * User: drevil
 * Date: 12/17/17
 * Time: 7:58 PM
 */

namespace App\Http\Controllers\Learner;

use App\Http\Controllers\ApiController;
use App\Http\Requests\ActivateLearner;
use App\Http\Requests\Learner\ForgotPasswordRequest;
use App\Http\Requests\Learner\ResetPasswordRequest;
use App\Mail\PasswordResetSent;
use App\Models\Learner;
use App\Models\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Resources\Learner as LearnerResource;
use Carbon\Carbon;

class AuthController extends ApiController
{
    protected $user = null;

    protected $gate = 'learner';

    /**
     * AuthController constructor.
     */
    public function __construct()
    {
        $this->user = new Learner();
        Auth::shouldUse('learner');
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

        $claims = ['type' => 'learner', 'email' => $credentials['email']];

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
            [ 'data' => new LearnerResource($user) ]
        );
    }

    /**
     * Validate and redirect the user to front end
     *
     * @param Request $request Http Request Object
     *
     * @return mixed
     */
    public function register(Request $request)
    {
        $email = htmlspecialchars($request->get('email'));
        $token = htmlspecialchars($request->get('token'));

        if (!$email || !$token) {
            return Redirect::to(env('LEARNER_URL').'/login');
        }

        if ($user = Learner::isValidInvitedLearner($email, $token)) {
            $url = '/setup-password?email='.$email.'&token='.$token;
            return Redirect::to(env('LEARNER_URL').$url);
        };

        return Redirect::to(env('LEARNER_URL').'/login');
    }


    /**
     * Activate Learner
     *
     * @param ActivateLearner $activateLearner
     *
     * @return mixed
     */
    public function activate(ActivateLearner $activateLearner)
    {

        if ($learner = Learner::isValidInvitedLearner($activateLearner->email, $activateLearner->token)) {
            if ($learner->activate($activateLearner)) {
                try {
                    $token = JWTAuth::claims($learner->generateJWTClaims())
                        ->attempt($learner->generateJWTCredentials($activateLearner->email, $activateLearner->password));

                    $learner->setJWT($token);

                } catch (JWTException $e) {
                    return Response::json(['error' => 'token_exception'], 500);
                }

                return $this->respond([
                    'success' => ['message' => 'Account has been activated!'],
                    'token' => $token
                ]);
            }

            return $this->respondForBadRequest('Can not activate');

        }

        return $this->respondForBadRequest('Invalid Email or Token');
    }


    /**
     * Validate and redirect the user to front end password reset page
     *
     * @param Request $request Http Request Object
     *
     * @return mixed
     */
    public function resetPasswordHandler(Request $request)
    {
        $email = htmlspecialchars($request->get('email'));
        $token = htmlspecialchars($request->get('token'));

        if (!$email || !$token) {
            return Redirect::to(env('LEARNER_URL').'/login');
        }

        try {
            if ($password_reset = PasswordReset::isValid($email, $token)) {
                if ($password_reset->notExpired()) {
                    return Redirect::to(env('LEARNER_URL').'/reset-password?email='.$email.'&token='.$token);
                }

            }

            return Redirect::to(env('LEARNER_URL').'/login');

        } catch (\Exception $e) {
            return $this->respondInternalError($e->getMessage());
        }
    }

    /**
     * Password Reset
     *
     * @param ResetPasswordRequest $resetPasswordRequest
     *
     * @return mixed
     */
    public function resetPassword(ResetPasswordRequest $resetPasswordRequest)
    {
        try {
            DB::beginTransaction();

            if (! Learner::getActiveAccountByEmail($resetPasswordRequest->email)) {
                return $this->respondForUnauthorizedRequest('This email address is not valid');
            }

            if ($password_reset = PasswordReset::isValid($resetPasswordRequest->email, $resetPasswordRequest->token)) {
                if ($password_reset->notExpired()) {
                    $learner = $password_reset->passwordResettable()->first();

                    if ($learner->isValidRegisteredLearner()) {
                        $learner->fill([
                            'password' => bcrypt($resetPasswordRequest->password)
                        ])->save();

                        $password_reset->fill([
                            'is_changed' => true
                        ])->save();

                        $token = JWTAuth::claims($learner->generateJWTClaims())
                                ->attempt($learner->generateJWTCredentials($resetPasswordRequest->email, $resetPasswordRequest->password));

                        $learner->setJWT($token);

                        DB::commit();

                        return $this->respond([
                            'success' => ['message' => 'Successfully updated!'],
                            'token' => $token
                        ]);

                    }

                    return $this->respondForUnauthorizedRequest('Inactive learner');

                }

                return $this->respondForUnauthorizedRequest('The link has expired');

            }

            return $this->respondForUnauthorizedRequest('The link has expired');

        } catch (\Exception $e) {
            DB::rollback();
            return $this->respondInternalError($e->getMessage());
        }
    }

    /**
     * Forgot user password
     *
     * @param ForgotPasswordRequest $forgotPasswordRequest
     *
     * @return mixed
     *
     */
    public function forgotPassword(ForgotPasswordRequest $forgotPasswordRequest)
    {
        try {
            if ($learner = Learner::isValidAndRegistered($forgotPasswordRequest->email)) {
                $passwordReset = new PasswordReset;

                $passwordReset->fill([
                    'email' => $forgotPasswordRequest->email,
                    'token' => md5($forgotPasswordRequest->email.Carbon::now()->toAtomString()),
                    'is_changed' => false,
                ]);

                $learner->passwordReset()->save($passwordReset);

                Mail::to($learner)
                    ->queue(new PasswordResetSent($learner, $passwordReset->token));

                return $this->respondSuccess('Please check your email to reset your password');

            }

            return $this->respondForUnauthorizedRequest('This email address is not valid');

        } catch (\Exception $e) {
            return $this->respondInternalError($e->getMessage());
        }
    }

}
